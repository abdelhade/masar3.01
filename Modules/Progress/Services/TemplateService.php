<?php

namespace Modules\Progress\Services;

use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\Subproject;
use Modules\Progress\Repositories\ProjectItemRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemplateService
{
    protected ProjectItemRepository $itemRepository;

    public function __construct(ProjectItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Create template items (same logic as ProjectService->createProjectItems)
     */
    public function createTemplateItems(ProjectTemplate $template, array $items, array $validated): array
    {
        $itemMapping = [];
        $order = 0;

        foreach ($items as $itemId => $item) {
            // Skip if work_item_id is missing
            if (empty($item['work_item_id'])) {
                continue;
            }

            // الحصول على lag من work_item إذا لم يتم تحديده
            $lag = $item['lag'] ?? 0;
            if ($lag === '' || $lag === null) {
                $workItem = $this->itemRepository->getWorkItemById($item['work_item_id']);
                $lag = $workItem->lag ?? 0;
            }

            $startDate = $item['start_date'] ?? $validated['start_date'] ?? now()->format('Y-m-d');
            $endDate = $item['end_date'] ?? $validated['end_date'] ?? now()->addDays(30)->format('Y-m-d');

            // Get default_quantity (same as total_quantity in projects)
            $defaultQuantity = isset($item['default_quantity']) && $item['default_quantity'] !== '' 
                ? (float)$item['default_quantity'] 
                : (isset($item['total_quantity']) && $item['total_quantity'] !== '' 
                    ? (float)$item['total_quantity'] 
                    : 1);

            $projectItem = ProjectItem::create([
                'project_id' => null, // Template items don't have project_id
                'project_template_id' => $template->id,
                'work_item_id' => $item['work_item_id'],
                'total_quantity' => $defaultQuantity,
                'completed_quantity' => 0, // Template items have no progress
                'remaining_quantity' => $defaultQuantity,
                'estimated_daily_qty' => isset($item['estimated_daily_qty']) && $item['estimated_daily_qty'] !== '' 
                    ? (float)$item['estimated_daily_qty'] 
                    : 0,
                'duration' => isset($item['duration']) && $item['duration'] !== '' 
                    ? (int)$item['duration'] 
                    : 0,
                'item_label' => $item['item_label'] ?? null,
                'daily_quantity' => $item['daily_quantity'] ?? null,
                'shift' => $item['shift'] ?? null,
                'predecessor' => null, // Will be set in second pass
                'dependency_type' => $item['dependency_type'] ?? 'end_to_start',
                'lag' => (int)$lag,
                'notes' => $item['notes'] ?? null,
                'is_measurable' => isset($item['is_measurable']) ? (bool)$item['is_measurable'] : false,
                'subproject_name' => $item['subproject_name'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'planned_end_date' => $item['planned_end_date'] ?? $endDate,
                'item_order' => $order++,
            ]);

            $itemMapping[$itemId] = $projectItem->id;
        }

        return $itemMapping;
    }

    /**
     * Sync template items (same logic as ProjectService->syncProjectItems)
     */
    public function syncTemplateItems(ProjectTemplate $template, array $items, array $validated): array
    {
        Log::info('===== SYNC TEMPLATE ITEMS START =====');
        Log::info('Items received', ['items' => array_map(function($item) {
            return [
                'id' => $item['id'] ?? 'NEW',
                'work_item_id' => $item['work_item_id'] ?? null,
                'predecessor' => $item['predecessor'] ?? 'NONE'
            ];
        }, $items)]);
        
        // ✅ احفظ البنود الموجودة قبل أي تعديل
        $existingItemIdsBeforeSync = $template->items()->pluck('id')->toArray();
        Log::info('Existing items before sync', ['ids' => $existingItemIdsBeforeSync]);
        
        // ✅ احصل على item_order الأصلي للبنود الموجودة
        $existingItemsOrder = [];
        if (!empty($existingItemIdsBeforeSync)) {
            $existingItemsOrder = ProjectItem::whereIn('id', $existingItemIdsBeforeSync)
                ->pluck('item_order', 'id')
                ->toArray();
        }
        
        // ✅ رتب البنود حسب item_order الأصلي (للبنود الموجودة) ثم البنود الجديدة
        if (!empty($existingItemsOrder)) {
            uasort($items, function($a, $b) use ($existingItemsOrder) {
                $aId = !empty($a['id']) && is_numeric($a['id']) && $a['id'] !== 'NEW' ? (int)$a['id'] : null;
                $bId = !empty($b['id']) && is_numeric($b['id']) && $b['id'] !== 'NEW' ? (int)$b['id'] : null;
                
                $aOrder = $aId && isset($existingItemsOrder[$aId]) ? $existingItemsOrder[$aId] : PHP_INT_MAX;
                $bOrder = $bId && isset($existingItemsOrder[$bId]) ? $existingItemsOrder[$bId] : PHP_INT_MAX;
                
                return $aOrder <=> $bOrder;
            });
        }
        
        $itemMapping = [];
        $order = 0;
        $submittedItemIds = [];

        foreach ($items as $itemId => $item) {
            // Skip if work_item_id is missing
            if (empty($item['work_item_id'])) {
                continue;
            }

            // الحصول على lag من work_item إذا لم يتم تحديده
            $lag = $item['lag'] ?? 0;
            if ($lag === '' || $lag === null) {
                $workItem = $this->itemRepository->getWorkItemById($item['work_item_id']);
                $lag = $workItem->lag ?? 0;
            }

            $startDate = $item['start_date'] ?? $validated['start_date'] ?? now()->format('Y-m-d');
            $endDate = $item['end_date'] ?? $validated['end_date'] ?? now()->addDays(30)->format('Y-m-d');

            // Get default_quantity
            $defaultQuantity = isset($item['default_quantity']) && $item['default_quantity'] !== '' 
                ? (float)$item['default_quantity'] 
                : (isset($item['total_quantity']) && $item['total_quantity'] !== '' 
                    ? (float)$item['total_quantity'] 
                    : 1);

            // ✅ إذا البند له ID موجود → UPDATE
            if (!empty($item['id']) && is_numeric($item['id']) && $item['id'] !== 'NEW') {
                $existingItemId = (int)$item['id'];
                $submittedItemIds[] = $existingItemId;
                
                // Template items don't have completed_quantity (always 0)
                $itemData = [
                    'work_item_id' => $item['work_item_id'],
                    'total_quantity' => $defaultQuantity,
                    'estimated_daily_qty' => isset($item['estimated_daily_qty']) && $item['estimated_daily_qty'] !== '' 
                        ? (float)$item['estimated_daily_qty'] 
                        : 0,
                    'duration' => isset($item['duration']) && $item['duration'] !== '' 
                        ? (int)$item['duration'] 
                        : 0,
                    'item_label' => $item['item_label'] ?? null,
                    'daily_quantity' => $item['daily_quantity'] ?? null,
                    'shift' => $item['shift'] ?? null,
                    'remaining_quantity' => $defaultQuantity,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'planned_end_date' => $item['planned_end_date'] ?? $endDate,
                    'dependency_type' => $item['dependency_type'] ?? 'end_to_start',
                    'lag' => (int)$lag,
                    'notes' => $item['notes'] ?? null,
                    'is_measurable' => isset($item['is_measurable']) ? (bool)$item['is_measurable'] : false,
                    'subproject_name' => $item['subproject_name'] ?? null,
                    'item_order' => $order++,
                ];
                
                $projectItem = $this->itemRepository->updateItem($existingItemId, $itemData);
                $itemMapping[$itemId] = $projectItem->id;
            } 
            // ✅ إذا البند جديد → CREATE
            else {
                Log::info('Creating NEW template item', [
                    'temp_key' => $itemId,
                    'work_item_id' => $item['work_item_id'],
                    'item_id_value' => $item['id'] ?? 'not set'
                ]);
                
                $projectItem = ProjectItem::create([
                    'project_id' => null,
                    'project_template_id' => $template->id,
                    'work_item_id' => $item['work_item_id'],
                    'total_quantity' => $defaultQuantity,
                    'completed_quantity' => 0,
                    'remaining_quantity' => $defaultQuantity,
                'duration' => isset($item['duration']) && $item['duration'] !== '' 
                    ? (int)$item['duration'] 
                    : 0,
                'item_label' => $item['item_label'] ?? null,
                'daily_quantity' => $item['daily_quantity'] ?? null,
                'shift' => $item['shift'] ?? null,
                'predecessor' => null, // Will be set in second pass
                'dependency_type' => $item['dependency_type'] ?? 'end_to_start',
                'lag' => (int)$lag,
                'notes' => $item['notes'] ?? null,
                'is_measurable' => isset($item['is_measurable']) ? (bool)$item['is_measurable'] : false,
                'subproject_name' => $item['subproject_name'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'planned_end_date' => $item['planned_end_date'] ?? $endDate,
                'item_order' => $order++,
                ]);
                $itemMapping[$itemId] = $projectItem->id;
                
                Log::info('NEW template item created successfully', [
                    'temp_key' => $itemId,
                    'new_project_item_id' => $projectItem->id
                ]);
            }
        }

        // ✅ حذف البنود القديمة اللي مش موجودة في الفورم
        $itemsToDelete = array_diff($existingItemIdsBeforeSync, $submittedItemIds);
        
        Log::info('Checking items to delete', [
            'existing_before_sync' => $existingItemIdsBeforeSync,
            'submitted' => $submittedItemIds,
            'to_delete' => $itemsToDelete
        ]);
        
        if (!empty($itemsToDelete)) {
            foreach ($itemsToDelete as $itemId) {
                Log::info('Deleting template item', ['item_id' => $itemId]);
                $this->itemRepository->deleteItem($itemId);
            }
        }

        Log::info('===== SYNC TEMPLATE ITEMS END =====');
        Log::info('Final Item Mapping', ['mapping' => $itemMapping]);

        return $itemMapping;
    }

    /**
     * Update item predecessors (same logic as ProjectService->updateItemPredecessors)
     */
    public function updateItemPredecessors(array $items, array $itemMapping): void
    {
        Log::info('===== PREDECESSOR PROCESSING START =====');
        Log::info('Item Mapping', ['mapping' => $itemMapping]);

        foreach ($items as $itemId => $item) {
            // تأكد إن البند موجود في الـ mapping
            if (!isset($itemMapping[$itemId])) {
                continue;
            }
            
            $projectItemId = (int)$itemMapping[$itemId];
            
            $predecessorValue = isset($item['predecessor']) && $item['predecessor'] !== ''
                ? trim($item['predecessor'])
                : null;

            // ✅ إذا فيه predecessor وموجود في الـ mapping
            if ($predecessorValue && isset($itemMapping[$predecessorValue])) {
                $realPredecessorId = (int)$itemMapping[$predecessorValue];
                $this->itemRepository->updateItemPredecessor($projectItemId, $realPredecessorId);

                Log::info('✅ Predecessor saved', [
                    'project_item_id' => $projectItemId,
                    'predecessor_id' => $realPredecessorId,
                    'temp_predecessor' => $predecessorValue,
                    'temp_item' => $itemId
                ]);
            } 
            // ✅ إذا مفيش predecessor، امسح القديم
            else {
                $this->itemRepository->updateItemPredecessor($projectItemId, null);
                Log::info('🧹 Predecessor cleared', ['project_item_id' => $projectItemId]);
            }
        }
    }

    /**
     * Create subprojects (same logic as ProjectService->createSubprojects)
     */
    public function createSubprojects(ProjectTemplate $template, array $subprojects): void
    {
        Log::info('===== CREATE TEMPLATE SUBPROJECTS =====');
        Log::info('Subprojects data received:', $subprojects);
        
        // Get subproject names that actually have items
        $subprojectNamesWithItems = $template->items()->whereNotNull('subproject_name')
            ->distinct()
            ->pluck('subproject_name')
            ->filter()
            ->toArray();
        
        Log::info('Subprojects with items:', $subprojectNamesWithItems);
        
        foreach ($subprojects as $subprojectData) {
            if (!empty($subprojectData['name'])) {
                // Only create subproject if it has items
                if (!in_array($subprojectData['name'], $subprojectNamesWithItems)) {
                    Log::info('Skipping empty subproject:', ['name' => $subprojectData['name']]);
                    continue;
                }
                
                $dataToCreate = [
                    'project_template_id' => $template->id,
                    'name' => $subprojectData['name'],
                    'start_date' => $subprojectData['start_date'] ?? null,
                    'end_date' => $subprojectData['end_date'] ?? null,
                    'total_quantity' => $subprojectData['total_quantity'] ?? 0,
                    'unit' => $subprojectData['unit'] ?? null,
                ];
                
                Log::info('Creating template subproject:', $dataToCreate);
                $created = Subproject::create($dataToCreate);
                Log::info('Template subproject created:', $created->toArray());
            }
        }
    }

    /**
     * Update subprojects (same logic as ProjectService->updateSubprojects)
     */
    public function updateSubprojects(ProjectTemplate $template, array $subprojects): void
    {
        Log::info('===== UPDATE TEMPLATE SUBPROJECTS =====');
        Log::info('Template ID: ' . $template->id);
        Log::info('Subprojects data received:', $subprojects);
        
        // Get subproject names that actually have items
        $subprojectNamesWithItems = $template->items()->whereNotNull('subproject_name')
            ->distinct()
            ->pluck('subproject_name')
            ->filter()
            ->toArray();
        
        Log::info('Subprojects with items:', $subprojectNamesWithItems);
        
        // Track which subprojects were submitted
        $submittedSubprojectNames = [];
        
        foreach ($subprojects as $subprojectData) {
            if (!empty($subprojectData['name'])) {
                $submittedSubprojectNames[] = $subprojectData['name'];
                
                // Only save subproject if it has items
                if (!in_array($subprojectData['name'], $subprojectNamesWithItems)) {
                    Log::info('Skipping empty subproject:', ['name' => $subprojectData['name']]);
                    
                    // Delete if it exists but now has no items
                    Subproject::where('project_template_id', $template->id)
                        ->where('name', $subprojectData['name'])
                        ->delete();
                    
                    continue;
                }
                
                $dataToUpdate = [
                    'start_date' => $subprojectData['start_date'] ?? null,
                    'end_date' => $subprojectData['end_date'] ?? null,
                    'total_quantity' => $subprojectData['total_quantity'] ?? 0,
                    'unit' => $subprojectData['unit'] ?? null,
                ];
                
                Log::info('Updating/Creating template subproject:', [
                    'name' => $subprojectData['name'],
                    'data' => $dataToUpdate
                ]);
                
                $subproject = Subproject::updateOrCreate(
                    [
                        'project_template_id' => $template->id,
                        'name' => $subprojectData['name']
                    ],
                    $dataToUpdate
                );
                
                Log::info('Template subproject updated/created:', $subproject->toArray());
            }
        }
        
        // Delete subprojects that were not submitted but have no items
        $orphanedSubprojects = Subproject::where('project_template_id', $template->id)
            ->whereNotIn('name', $subprojectNamesWithItems)
            ->get();
        
        foreach ($orphanedSubprojects as $orphan) {
            Log::info('Deleting orphaned template subproject:', ['name' => $orphan->name]);
            $orphan->delete();
        }
    }
}


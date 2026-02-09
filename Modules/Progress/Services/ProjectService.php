<?php

namespace Modules\Progress\Services;

use Modules\Progress\Repositories\ProjectRepository;
use Modules\Progress\Repositories\ProjectItemRepository;
use Modules\Progress\Models\ProjectProgress as Project;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\Subproject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    protected ProjectRepository $projectRepository;
    protected ProjectItemRepository $itemRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        ProjectItemRepository $itemRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->itemRepository = $itemRepository;
    }

    public function createProject(array $validated): Project
    {
        return DB::transaction(function () use ($validated) {
            // ✅ تحويل save_as_draft إلى boolean بشكل صحيح
            // save_as_draft قد يكون "1" (string) من النموذج أو true (boolean)
            $isDraft = isset($validated['save_as_draft']) && 
                       $validated['save_as_draft'] !== false && 
                       $validated['save_as_draft'] !== '0' && 
                       $validated['save_as_draft'] !== 0;

            // ✅ إنشاء المشروع بكل البيانات (سواء كان draft أم لا)
            // الفرق الوحيد هو قيمة is_draft
            $project = $this->projectRepository->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'working_zone' => $validated['working_zone'] ?? null,
                'project_type_id' => $validated['project_type_id'] ?? null,
                'working_days' => $validated['working_days'] ?? 6,
                'daily_work_hours' => $validated['daily_work_hours'] ?? 8,
                'weekly_holidays' => $validated['weekly_holidays'] ?? '',
                'is_draft' => $isDraft,
                'is_progress' => 1,
                'created_by' => $validated['created_by'] ?? auth()->id(),
            ]);

            // إضافة البنود
            if (!empty($validated['items'])) {
                $itemMapping = $this->createProjectItems($project, $validated['items'], $validated);
                $this->updateItemPredecessors($validated['items'], $itemMapping);
            }

            // ربط الموظفين
            if (!empty($validated['employees'])) {
                $this->projectRepository->syncEmployees($project, $validated['employees']);
            }

            // حفظ المشاريع الفرعية
            if (!empty($validated['subprojects'])) {
                $this->createSubprojects($project, $validated['subprojects']);
            }

            return $project;
        });
    }

    public function updateProject(Project $project, array $validated): Project
    {
        return DB::transaction(function () use ($project, $validated) {
            // ✅ تحديث المشروع بكل البيانات
            $this->projectRepository->update($project, [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'working_zone' => $validated['working_zone'] ?? null,
                'project_type_id' => $validated['project_type_id'] ?? null,
                'working_days' => $validated['working_days'] ?? 6,
                'daily_work_hours' => $validated['daily_work_hours'] ?? 8,
                'weekly_holidays' => $validated['weekly_holidays'] ?? '',
                'is_progress' =>  1,
            ]);

            // ✅ Smart Sync للبنود: يحافظ على daily_progress
            if (!empty($validated['items'])) {
                $itemMapping = $this->syncProjectItems($project, $validated['items'], $validated);
                $this->updateItemPredecessors($validated['items'], $itemMapping);
            } else {
                // إذا لم يتم إرسال بنود، احذف كل البنود القديمة
                $this->itemRepository->deleteAllItems($project);
            }

            // تحديث الموظفين
            if (!empty($validated['employees'])) {
                $this->projectRepository->syncEmployees($project, $validated['employees']);
            }

            // تحديث المشاريع الفرعية
            if (!empty($validated['subprojects'])) {
                $this->updateSubprojects($project, $validated['subprojects']);
            }

            return $project->fresh();
        });
    }

    /**
     * Smart Sync للبنود: يحافظ على البنود الموجودة والـ daily_progress المرتبطة بها
     */
    protected function syncProjectItems(Project $project, array $items, array $validated): array
    {
        Log::info('===== SYNC PROJECT ITEMS START =====');
        Log::info('Items received', ['items' => array_map(function($item) {
            return [
                'id' => $item['id'] ?? 'NEW',
                'work_item_id' => $item['work_item_id'] ?? null,
                'predecessor' => $item['predecessor'] ?? 'NONE'
            ];
        }, $items)]);
        
        // ✅ احفظ البنود الموجودة قبل أي تعديل
        $existingItemIdsBeforeSync = $project->items()->pluck('id')->toArray();
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
        
        Log::info('Items sorted by original item_order', ['sorted_items' => array_map(function($item) use ($existingItemsOrder) {
            $itemId = !empty($item['id']) && is_numeric($item['id']) && $item['id'] !== 'NEW' ? (int)$item['id'] : null;
            $originalOrder = $itemId && isset($existingItemsOrder[$itemId]) ? $existingItemsOrder[$itemId] : 'NEW';
            return [
                'id' => $item['id'] ?? 'NEW',
                'original_order' => $originalOrder
            ];
        }, $items)]);
        
        $itemMapping = [];
        $order = 0;
        $submittedItemIds = [];

        foreach ($items as $itemId => $item) {
            // تخطي البنود الفارغة في حالة المسودة
            if (($validated['save_as_draft'] ?? false) && empty($item['work_item_id'])) {
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

            // ✅ إذا البند له ID موجود → UPDATE (يحافظ على daily_progress)
            if (!empty($item['id']) && is_numeric($item['id']) && $item['id'] !== 'NEW') {
                $existingItemId = (int)$item['id'];
                $submittedItemIds[] = $existingItemId;
                
                // نحافظ على completed_quantity الحالية ونحسب remaining_quantity
                $existingItem = ProjectItem::find($existingItemId);
                $completedQty = $existingItem ? $existingItem->completed_quantity : 0;
                
                $itemData = [
                    'work_item_id' => $item['work_item_id'],
                    'total_quantity' => $item['total_quantity'] ?? 0,
                    'estimated_daily_qty' => $item['estimated_daily_qty'] ?? 0,
                    'duration' => $item['duration'] ?? 0,
                    'remaining_quantity' => ($item['total_quantity'] ?? 0) - $completedQty,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'planned_end_date' => $endDate,
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
                Log::info('Creating NEW item', [
                    'temp_key' => $itemId,
                    'work_item_id' => $item['work_item_id'],
                    'item_id_value' => $item['id'] ?? 'not set'
                ]);
                
                $itemData = [
                    'work_item_id' => $item['work_item_id'],
                    'total_quantity' => $item['total_quantity'] ?? 0,
                    'estimated_daily_qty' => $item['estimated_daily_qty'] ?? 0,
                    'duration' => $item['duration'] ?? 0,
                    'completed_quantity' => 0,
                    'remaining_quantity' => $item['total_quantity'] ?? 0,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'planned_end_date' => $endDate,
                    'dependency_type' => $item['dependency_type'] ?? 'end_to_start',
                    'lag' => (int)$lag,
                    'notes' => $item['notes'] ?? null,
                    'is_measurable' => isset($item['is_measurable']) ? (bool)$item['is_measurable'] : false,
                    'subproject_name' => $item['subproject_name'] ?? null,
                    'item_order' => $order++,
                ];
                
                $projectItem = $this->itemRepository->createItem($project, $itemData);
                $itemMapping[$itemId] = $projectItem->id;
                
                Log::info('NEW item created successfully', [
                    'temp_key' => $itemId,
                    'new_project_item_id' => $projectItem->id
                ]);
            }
        }

        // ✅ حذف البنود القديمة اللي مش موجودة في الفورم
        // استخدم القائمة اللي تم حفظها قبل التعديل
        $itemsToDelete = array_diff($existingItemIdsBeforeSync, $submittedItemIds);
        
        Log::info('Checking items to delete', [
            'existing_before_sync' => $existingItemIdsBeforeSync,
            'submitted' => $submittedItemIds,
            'to_delete' => $itemsToDelete
        ]);
        
        // ✅ التحقق من وجود daily_progress في البنود المحذوفة
        if (!empty($itemsToDelete)) {
            $itemsWithProgress = ProjectItem::whereIn('id', $itemsToDelete)
                ->whereHas('dailyProgress')
                ->with('workItem')
                ->get();
            
            if ($itemsWithProgress->isNotEmpty()) {
                $itemNames = $itemsWithProgress->map(fn($item) => $item->workItem->name ?? 'Unknown')->join(', ');
                
                Log::warning('Cannot delete items with daily progress', [
                    'items' => $itemsWithProgress->pluck('id')->toArray(),
                    'names' => $itemNames
                ]);
                
                throw new \Exception(
                    __('general.cannot_delete_items_with_progress') . ': ' . $itemNames
                );
            }
            
            // ✅ لو مفيش daily_progress، امسح البنود
            foreach ($itemsToDelete as $itemId) {
                Log::info('Deleting item without progress', ['item_id' => $itemId]);
                $this->itemRepository->deleteItem($itemId);
            }
        }

        Log::info('===== SYNC PROJECT ITEMS END =====');
        Log::info('Final Item Mapping', ['mapping' => $itemMapping]);

        return $itemMapping;
    }

    protected function createProjectItems(Project $project, array $items, array $validated): array
    {
        $itemMapping = [];
        $order = 0;

        foreach ($items as $itemId => $item) {
            // تخطي البنود الفارغة في حالة المسودة
            if (($validated['save_as_draft'] ?? false) && empty($item['work_item_id'])) {
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

            $projectItem = $this->itemRepository->createItem($project, [
                'work_item_id' => $item['work_item_id'],
                'total_quantity' => $item['total_quantity'] ?? 0,
                'estimated_daily_qty' => $item['estimated_daily_qty'] ?? 0,
                'duration' => $item['duration'] ?? 0,
                'completed_quantity' => 0,
                'remaining_quantity' => $item['total_quantity'] ?? 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'planned_end_date' => $endDate,
                'dependency_type' => $item['dependency_type'] ?? 'end_to_start',
                'lag' => (int)$lag,
                'notes' => $item['notes'] ?? null,
                'is_measurable' => isset($item['is_measurable']) ? (bool)$item['is_measurable'] : false,
                'subproject_name' => $item['subproject_name'] ?? null,
                'item_order' => $order++,
            ]);

            $itemMapping[$itemId] = $projectItem->id;
        }

        return $itemMapping;
    }

    protected function updateItemPredecessors(array $items, array $itemMapping): void
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

    protected function createSubprojects(Project $project, array $subprojects): void
    {
        Log::info('===== CREATE SUBPROJECTS =====');
        Log::info('Subprojects data received:', $subprojects);
        
        // Get subproject names that actually have items
        $subprojectNamesWithItems = $project->items()->whereNotNull('subproject_name')
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
                    'project_id' => $project->id,
                    'name' => $subprojectData['name'],
                    'start_date' => $subprojectData['start_date'] ?? null,
                    'end_date' => $subprojectData['end_date'] ?? null,
                    'total_quantity' => $subprojectData['total_quantity'] ?? 0,
                    'unit' => $subprojectData['unit'] ?? null,
                ];
                
                Log::info('Creating subproject:', $dataToCreate);
                $created = Subproject::create($dataToCreate);
                Log::info('Subproject created:', $created->toArray());
            }
        }
    }

    protected function updateSubprojects(Project $project, array $subprojects): void
    {
        Log::info('===== UPDATE SUBPROJECTS =====');
        Log::info('Project ID: ' . $project->id);
        Log::info('Subprojects data received:', $subprojects);
        
        // Get subproject names that actually have items
        $subprojectNamesWithItems = $project->items()->whereNotNull('subproject_name')
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
                    Subproject::where('project_id', $project->id)
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
                
                Log::info('Updating/Creating subproject:', [
                    'name' => $subprojectData['name'],
                    'data' => $dataToUpdate
                ]);
                
                $subproject = Subproject::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'name' => $subprojectData['name']
                    ],
                    $dataToUpdate
                );
                
                Log::info('Subproject updated/created:', $subproject->toArray());
            }
        }
        
        // Delete subprojects that were not submitted but have no items
        $orphanedSubprojects = Subproject::where('project_id', $project->id)
            ->whereNotIn('name', $subprojectNamesWithItems)
            ->get();
        
        foreach ($orphanedSubprojects as $orphan) {
            Log::info('Deleting orphaned subproject:', ['name' => $orphan->name]);
            $orphan->delete();
        }
    }

    public function deleteProject(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            // حذف البنود والتقدم اليومي
            $project->items()->each(function ($item) {
                if (method_exists($item, 'dailyProgress')) {
                    $item->dailyProgress()->delete();
                }
                $item->delete();
            });

            return $this->projectRepository->delete($project);
        });
    }

    public function publishDraft(Project $project): array
    {
        $errors = [];

        if (!$project->is_draft) {
            $errors[] = __('general.project_is_not_draft');
            return ['success' => false, 'errors' => $errors];
        }

        // Validate required data
        if (!$project->client_id) $errors[] = __('general.client_required');
        if (!$project->start_date) $errors[] = __('general.start_date_required');
        if (!$project->end_date) $errors[] = __('general.end_date_required');
        if (!$project->project_type_id) $errors[] = __('general.project_type_required');
        if (!$project->working_zone) $errors[] = __('general.working_zone_required');
        if ($project->items->count() === 0) $errors[] = __('general.items_required');
        if ($project->employees->count() === 0) $errors[] = __('general.employees_required');

        if (count($errors) > 0) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->projectRepository->update($project, ['is_draft' => false]);

        return ['success' => true];
    }

    public function copyProject(Project $project, string $newName): Project
    {
        return DB::transaction(function () use ($project, $newName) {
            // ✅ تحميل جميع العلاقات المطلوبة
            $project->load(['items', 'employees', 'subprojects']);

            $newProject = $this->projectRepository->create([
                'name' => $newName,
                'description' => $project->description,
                'client_id' => $project->client_id,
                'status' => 'pending',
                'project_type_id' => $project->project_type_id,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'working_days' => $project->working_days ?? 6,
                'daily_work_hours' => $project->daily_work_hours ?? 8,
                'weekly_holidays' => $project->weekly_holidays ?? '',
                'working_zone' => $project->working_zone,
                'is_draft' => true
            ]);

            // ✅ نسخ البنود مع إنشاء mapping للعلاقات (predecessor)
            $itemMapping = []; // [old_item_id => new_item_id]
            
            if ($project->items && $project->items->count() > 0) {
                // ترتيب البنود حسب item_order للحفاظ على الترتيب
                $sortedItems = $project->items->sortBy('item_order');
                
                foreach ($sortedItems as $item) {
                    $newItem = $this->itemRepository->createItem($newProject, [
                        'work_item_id' => $item->work_item_id,
                        'total_quantity' => $item->total_quantity ?? 0,
                        'estimated_daily_qty' => $item->estimated_daily_qty ?? 0,
                        'duration' => $item->duration ?? 0, // ✅ نسخ duration
                        'completed_quantity' => 0,
                        'remaining_quantity' => $item->total_quantity ?? 0,
                        'predecessor' => null, // ✅ سيتم تحديثه لاحقًا بعد إنشاء جميع البنود
                        'dependency_type' => $item->dependency_type ?? 'end_to_start',
                        'lag' => $item->lag ?? 0,
                        'notes' => $item->notes,
                        'is_measurable' => $item->is_measurable ?? false,
                        'item_order' => $item->item_order ?? 0,
                        'subproject_name' => $item->subproject_name, // ✅ نسخ subproject_name
                        'start_date' => $item->start_date,
                        'end_date' => $item->end_date,
                        'planned_end_date' => $item->planned_end_date
                    ]);
                    
                    // ✅ حفظ mapping للعلاقات
                    $itemMapping[$item->id] = $newItem->id;
                }
                
                // ✅ تحديث العلاقات (predecessor) للبنود الجديدة
                foreach ($sortedItems as $oldItem) {
                    if ($oldItem->predecessor && isset($itemMapping[$oldItem->id]) && isset($itemMapping[$oldItem->predecessor])) {
                        $newItemId = $itemMapping[$oldItem->id];
                        $newPredecessorId = $itemMapping[$oldItem->predecessor];
                        $this->itemRepository->updateItemPredecessor($newItemId, $newPredecessorId);
                    }
                }
            }

            // ✅ نسخ الموظفين
            if ($project->employees && $project->employees->count() > 0) {
                $this->projectRepository->syncEmployees($newProject, $project->employees->pluck('id')->toArray());
            }

            // ✅ نسخ المشاريع الفرعية
            if ($project->subprojects && $project->subprojects->count() > 0) {
                foreach ($project->subprojects as $subproject) {
                    Subproject::create([
                        'project_id' => $newProject->id,
                        'name' => $subproject->name,
                        'start_date' => $subproject->start_date,
                        'end_date' => $subproject->end_date,
                        'total_quantity' => $subproject->total_quantity ?? 0,
                        'unit' => $subproject->unit,
                    ]);
                }
            }

            return $newProject;
        });
    }
}

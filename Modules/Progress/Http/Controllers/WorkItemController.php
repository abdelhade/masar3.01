<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\WorkItem;
use Illuminate\Http\Request;
use Modules\Progress\Models\WorkItemCategory;

class WorkItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view progress-work-items')->only(['index', 'search']);
        $this->middleware('can:create progress-work-items')->only(['create', 'store']);
        $this->middleware('can:edit progress-work-items')->only(['edit', 'update', 'reorder']);
        $this->middleware('can:delete progress-work-items')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $perPage = $request->get('per_page', 50); // Default 50 items per page
        
        $query = WorkItem::with('category')->orderBy('order');
        
        // Add search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by category
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $workItems = $query->paginate($perPage)->withQueryString();
        
        // Get all categories for filter dropdown
        $categories = WorkItemCategory::orderBy('name')->get();
        
        return view('progress::work-items.index', compact('workItems', 'categories'));
    }

    public function reorder(Request $request)
{
    $items = $request->order;

    foreach ($items as $item) {
        \Modules\Progress\Models\WorkItem::where('id', $item['id'])->update(['order' => $item['position']]);
    }

    return response()->json(['success' => true]);
}

    public function create()
    {
    $categories = WorkItemCategory::all();
        return view('progress::work-items.create', compact('categories'));
    }

    public function store(Request $request)
    {
     $request->validate([
        'name' => 'required|string|max:255',
         'unit' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category_id' => 'required|exists:work_item_categories,id',
        ]);

    WorkItem::create($request->only(['name', 'unit', 'description', 'category_id']));

        return redirect()->route('work-items.index')
            ->with('success', 'Work item added successfully');
    }

    public function show(WorkItem $workItem)
    {
        return view('progress::work-items.show', compact('workItem'));
    }

    public function edit(WorkItem $workItem)
    {
           $categories = WorkItemCategory::all();
        return view('progress::work-items.edit', compact('workItem', 'categories'));
    }

    public function update(Request $request, WorkItem $workItem)
    {
     $request->validate([
        'name' => 'required|string|max:255',
         'unit' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category_id' => 'required|exists:work_item_categories,id',
        ]);

        $workItem->update($request->all());

        return redirect()->route('work-items.index')
            ->with('success', 'Work item updated successfully');
    }

    public function destroy(WorkItem $workItem)
    {
        $workItem->delete();

        return redirect()->route('work-items.index')
            ->with('success', 'تم حذف بند العمل بنجاح');
    }

    /**
     * AJAX endpoint for Dynamic Search - Get work items with search and pagination
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 25; // Fixed at 25 items per request
        
        $query = WorkItem::select('id', 'name', 'unit', 'category_id', 'description', 'order')
            ->with(['category:id,name']);
            
        // Apply search if term is provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($catQuery) use ($search) {
                      $catQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $workItems = $query->orderBy('order')
                          ->paginate($perPage, ['*'], 'page', $page);

        // Format results for dynamic search
        $results = $workItems->map(function($item) {
            return [
                'id' => $item->id,
                'text' => $item->name . ' (' . $item->unit . ')',
                'name' => $item->name,
                'unit' => $item->unit,
                'category' => $item->category ? $item->category->name : __('general.uncategorized'),
                'category_id' => $item->category_id,
                'description' => $item->description ?: '',
                'order' => $item->order,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $workItems->hasMorePages(),
                'current_page' => $workItems->currentPage(),
                'last_page' => $workItems->lastPage(),
                'total' => $workItems->total(),
                'per_page' => $workItems->perPage()
            ]
        ]);
    }
}

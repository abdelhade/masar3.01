<?php

namespace Modules\Inquiries\Http\Controllers;

use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\WorkType;
use Modules\Inquiries\Http\Requests\WorkTypeRequest;
use Illuminate\Http\Request;
use Exception;

class WorkTypeController extends Controller
{
    public function index()
    {
        try {
            $workTypes = WorkType::whereNull('parent_id')
                ->with('childrenRecursive')
                ->orderBy('name')
                ->get();

            $workTypesTree = $this->buildTreeArray($workTypes);

            return view('inquiries::work-types.index', compact('workTypes', 'workTypesTree'));
        } catch (Exception $e) {
            Alert::toast('حدث خطأ في تحميل البيانات', 'error');
            return redirect()->back();
        }
    }

    public function store(WorkTypeRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['is_active'] = $validatedData['is_active'] ?? true;

            $workType = WorkType::create($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء نوع العمل بنجاح',
                    'workType' => $this->formatWorkTypeForResponse($workType)
                ]);
            }

            Alert::toast('تم إنشاء نوع العمل بنجاح', 'success');
            return redirect()->route('work.types.index');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حفظ نوع العمل'
                ], 500);
            }

            Alert::toast('حدث خطأ أثناء حفظ نوع العمل', 'error');
            return redirect()->back()->withInput();
        }
    }

    public function update(WorkTypeRequest $request, $id)
    {
        try {
            $workType = WorkType::findOrFail($id);
            $validatedData = $request->validated();

            // التحقق من عدم جعل نوع العمل فرع من نفسه أو من أحد فروعه
            if (isset($validatedData['parent_id']) && $validatedData['parent_id']) {
                if ($this->isDescendant($workType, $validatedData['parent_id'])) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'لا يمكن جعل نوع العمل فرع من نفسه أو من أحد فروعه'
                        ], 400);
                    }

                    Alert::toast('لا يمكن جعل نوع العمل فرع من نفسه أو من أحد فروعه', 'error');
                    return redirect()->back()->withInput();
                }
            }

            $workType->update($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تعديل نوع العمل بنجاح',
                    'workType' => $this->formatWorkTypeForResponse($workType)
                ]);
            }

            Alert::toast('تم تعديل نوع العمل بنجاح', 'success');
            return redirect()->route('work.types.index');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تعديل نوع العمل'
                ], 500);
            }

            Alert::toast('حدث خطأ أثناء تعديل نوع العمل', 'error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $workType = WorkType::findOrFail($id);

            $childrenCount = $workType->childrenRecursive()->count();
            $this->deleteWorkTypeAndChildren($workType);

            return response()->json([
                'success' => true,
                'message' => $childrenCount > 0
                    ? 'تم حذف نوع العمل و ' . $childrenCount . ' فرع تابع بنجاح'
                    : 'تم حذف نوع العمل بنجاح'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف نوع العمل'
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $workType = WorkType::findOrFail($id);
            $workType->is_active = !$workType->is_active;
            $workType->save();

            return response()->json([
                'success' => true,
                'is_active' => $workType->is_active,
                'message' => $workType->is_active
                    ? 'تم تفعيل نوع العمل بنجاح'
                    : 'تم إلغاء تفعيل نوع العمل بنجاح'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير حالة نوع العمل'
            ], 500);
        }
    }

    public function getTreeData()
    {
        try {
            $workTypes = WorkType::whereNull('parent_id')
                ->with('childrenRecursive')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $this->buildTreeArray($workTypes)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل بيانات أنواع العمل',
                'data' => []
            ], 500);
        }
    }

    public function getActiveWorkTypes(Request $request)
    {
        try {
            $query = WorkType::where('is_active', true);

            if ($request->has('parent_id')) {
                if ($request->parent_id === 'null' || $request->parent_id === '') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            $workTypes = $query->orderBy('name')->get(['id', 'name', 'parent_id']);

            return response()->json([
                'success' => true,
                'data' => $workTypes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل أنواع العمل',
                'data' => []
            ], 500);
        }
    }

    private function buildTreeArray($workTypes)
    {
        return $workTypes->map(function ($workType) {
            return [
                'id' => $workType->id,
                'name' => $workType->name,
                'parent_id' => $workType->parent_id,
                'is_active' => $workType->is_active,
                'created_at' => $workType->created_at->format('Y-m-d H:i'),
                'updated_at' => $workType->updated_at->format('Y-m-d H:i'),
                'children' => $this->buildTreeArray($workType->childrenRecursive)
            ];
        })->toArray();
    }

    private function formatWorkTypeForResponse($workType)
    {
        $workType->load('parent', 'childrenRecursive');

        return [
            'id' => $workType->id,
            'name' => $workType->name,
            'parent_id' => $workType->parent_id,
            'parent_name' => $workType->parent ? $workType->parent->name : null,
            'is_active' => $workType->is_active,
            'has_children' => $workType->childrenRecursive->count() > 0,
            'children_count' => $workType->childrenRecursive->count(),
            'created_at' => $workType->created_at->format('Y-m-d H:i'),
            'updated_at' => $workType->updated_at->format('Y-m-d H:i'),
            'path' => $this->buildWorkTypePath($workType)
        ];
    }

    private function buildWorkTypePath($workType)
    {
        $path = [$workType];
        $current = $workType;

        while ($current->parent_id) {
            $current = $current->parent;
            if ($current) {
                array_unshift($path, $current);
            } else {
                break;
            }
        }

        return array_map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name
            ];
        }, $path);
    }

    private function isDescendant($workType, $potentialParentId)
    {
        if ($workType->id == $potentialParentId) {
            return true;
        }

        $descendants = $this->getAllDescendants($workType);
        return $descendants->pluck('id')->contains($potentialParentId);
    }

    private function getAllDescendants($workType)
    {
        $descendants = collect();

        foreach ($workType->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($this->getAllDescendants($child));
        }

        return $descendants;
    }

    private function deleteWorkTypeAndChildren($workType)
    {
        foreach ($workType->children as $child) {
            $this->deleteWorkTypeAndChildren($child);
        }
        $workType->delete();
    }
}

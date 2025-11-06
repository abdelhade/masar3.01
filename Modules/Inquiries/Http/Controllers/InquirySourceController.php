<?php

namespace Modules\Inquiries\Http\Controllers;

use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\InquirySource;
use Modules\Inquiries\Http\Requests\InquirySourceRequest;

class InquirySourceController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:View Inquiries Source')->only(['index', 'getTreeData']);
        $this->middleware('can:Create Inquiries Source')->only('store');
        $this->middleware('can:Edit Inquiries Source')->only('update');
        $this->middleware('can:Delete Inquiries Source')->only('destroy');
    }
    public function index()
    {
        $sources = InquirySource::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        $sourcesTree = $this->buildTreeArray($sources);

        return view('inquiries::inquiry-sources.index', compact('sources', 'sourcesTree'));
    }

    public function store(InquirySourceRequest $request)
    {
        try {
            $source = InquirySource::create($request->validated());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Source created successfully'),
                    'source' => $this->formatSourceForResponse($source)
                ]);
            }
            return redirect()->route('inquiry.sources.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Error during save')
                ], 500);
            }

            Alert::toast(__('Error during save'), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function update(InquirySourceRequest $request, $id)
    {
        try {
            $source = InquirySource::findOrFail($id);

            // التحقق من عدم جعل المصدر فرع من نفسه أو من أحد فروعه
            if ($request->parent_id && $this->isDescendant($source, $request->parent_id)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Cannot make source child of itself or its children')
                    ], 400);
                }

                Alert::toast(__('Cannot make source child of itself or its children'), 'error');
                return redirect()->back()->withInput();
            }

            $source->update($request->validated());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Source updated successfully'),
                    'source' => $this->formatSourceForResponse($source)
                ]);
            }

            Alert::toast(__('Source updated successfully'), 'success');
            return redirect()->route('inquiry.sources.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Error during update')
                ], 500);
            }

            Alert::toast(__('Error during update'), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $source = InquirySource::findOrFail($id);

            $this->deleteSourceAndChildren($source);

            return response()->json([
                'success' => true,
                'message' => __('Source and children deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error during delete')
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $source = InquirySource::findOrFail($id);
            $source->is_active = !$source->is_active;
            $source->save();

            return response()->json([
                'success' => true,
                'is_active' => $source->is_active,
                'message' => $source->is_active ? __('Source activated') : __('Source deactivated')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error during status change')
            ], 500);
        }
    }

    public function getTreeData()
    {
        $sources = InquirySource::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $this->buildTreeArray($sources)
        ]);
    }

    private function buildTreeArray($sources)
    {
        return $sources->map(function ($source) {
            return [
                'id' => $source->id,
                'name' => $source->name,
                'parent_id' => $source->parent_id,
                'is_active' => $source->is_active,
                'created_at' => $source->created_at->format('Y-m-d H:i'),
                'children' => $this->buildTreeArray($source->childrenRecursive)
            ];
        })->toArray();
    }

    private function formatSourceForResponse($source)
    {
        $source->load('parent', 'childrenRecursive');

        return [
            'id' => $source->id,
            'name' => $source->name,
            'parent_id' => $source->parent_id,
            'parent_name' => $source->parent ? $source->parent->name : null,
            'is_active' => $source->is_active,
            'has_children' => $source->childrenRecursive->count() > 0,
            'children_count' => $source->childrenRecursive->count(),
            'created_at' => $source->created_at->format('Y-m-d H:i'),
            'path' => $this->buildSourcePath($source)
        ];
    }

    private function buildSourcePath($source)
    {
        $path = [$source];
        $current = $source;

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

    private function isDescendant($source, $potentialParentId)
    {
        if ($source->id == $potentialParentId) {
            return true;
        }

        $descendants = $this->getAllDescendants($source);
        return $descendants->pluck('id')->contains($potentialParentId);
    }

    private function getAllDescendants($source)
    {
        $descendants = collect();

        foreach ($source->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($this->getAllDescendants($child));
        }

        return $descendants;
    }

    private function deleteSourceAndChildren($source)
    {
        foreach ($source->children as $child) {
            $this->deleteSourceAndChildren($child);
        }
        $source->delete();
    }
}

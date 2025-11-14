<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Http\Requests\ClientCategoryRequest;
use Modules\CRM\Models\ClientCategory;
use RealRashid\SweetAlert\Facades\Alert;

class ClientCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Client Categories')->only(['index']);
        $this->middleware('can:create Client Categories')->only(['create', 'store']);
        $this->middleware('can:edit Client Categories')->only(['edit', 'update']);
        $this->middleware('can:delete Client Categories')->only(['destroy']);
    }

    public function index()
    {
        $categories = ClientCategory::paginate(20);
        return view('crm::client-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('crm::client-categories.create');
    }

    public function store(ClientCategoryRequest $request)
    {
        ClientCategory::create($request->validated());
        Alert::toast(__('Category created successfully'), 'success');
        return redirect()->route('client.categories.index');
    }

    public function edit(ClientCategory $category)
    {
        return view('crm::client-categories.edit', compact('category'));
    }

    public function update(ClientCategoryRequest $request, ClientCategory $category)
    {
        $category->update($request->validated());
        Alert::toast(__('Category updated successfully'), 'success');
        return redirect()->route('client.categories.index');
    }

    public function destroy(ClientCategory $category)
    {
        try {
            $category->delete();
            Alert::toast(__('Category deleted successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the category'), 'error');
        }
        return redirect()->route('client.categories.index');
    }
}

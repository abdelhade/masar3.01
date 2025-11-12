<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Models\ClientCategory;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\ClientCategoryRequest;

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
        $categories = ClientCategory::all();
        return view('crm::client-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('crm::client-categories.create');
    }

    public function store(ClientCategoryRequest $request)
    {
        ClientCategory::create($request->validated());
        Alert::toast('تم الإنشاء بنجاح', 'success');
        return redirect()->route('client.categories.index');
    }

    public function edit($id)
    {
        $category = ClientCategory::findOrFail($id);
        return view('crm::client-categories.edit', compact('category'));
    }

    public function update(ClientCategoryRequest $request, $id)
    {
        $category = ClientCategory::findOrFail($id);
        $category->update($request->validated());
        Alert::toast('تم التعديل بنجاح', 'success');
        return redirect()->route('client.categories.index');
    }

    public function destroy($id)
    {
        $category = ClientCategory::findOrFail($id);
        $category->delete();
        Alert::toast('تم الحذف بنجاح', 'success');
        return redirect()->route('client.categories.index');
    }
}

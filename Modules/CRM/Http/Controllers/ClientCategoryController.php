<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\CRM\Models\ClientCategory;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\ClientCategoryRequest;

class ClientCategoryController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:عرض تصنيفات العملاء')->only(['index']);
    //     $this->middleware('can:إضافة تصنيف عميل')->only(['create', 'store']);
    //     $this->middleware('can:تعديل تصنيف عميل')->only(['edit', 'update']);
    //     $this->middleware('can:حذف تصنيف عميل')->only(['destroy']);
    // }

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

<?php

namespace Modules\Inquiries\Http\Controllers;

use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\InquiryDocument;
use Modules\Inquiries\Http\Requests\InquiryDocumentRequest;

class InquiryDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Documents')->only('index');
        $this->middleware('can:create Documents')->only(['create', 'store']);
        $this->middleware('can:edit Documents')->only(['edit', 'update']);
        $this->middleware('can:delete Documents')->only('destroy');
    }

    public function index()
    {
        $documents = InquiryDocument::latest()->get();
        return view('inquiries::project-documents.index', compact('documents'));
    }

    public function create()
    {
        return view('inquiries::project-documents.create');
    }

    public function store(InquiryDocumentRequest $request)
    {
        try {
            InquiryDocument::create($request->validated());
            Alert::toast(__('Document created successfully'), 'success');
            return redirect()->route('inquiry.documents.index');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred while creating the document'), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $document = InquiryDocument::findOrFail($id);
        return view('inquiries::project-documents.edit', compact('document'));
    }

    public function update(InquiryDocumentRequest $request, $id)
    {
        try {
            $document = InquiryDocument::findOrFail($id);
            $document->update($request->validated());
            Alert::toast(__('Document updated successfully'), 'success');
            return redirect()->route('inquiry.documents.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while updating the document'), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $document = InquiryDocument::findOrFail($id);
            $document->delete();
            Alert::toast(__('Document deleted successfully'), 'success');
            return redirect()->route('inquiry.documents.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the document'), 'error');
            return redirect()->back();
        }
    }
}

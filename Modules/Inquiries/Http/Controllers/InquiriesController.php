<?php

namespace Modules\Inquiries\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InquiriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inquiries::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inquiries::inquiries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('inquiries::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('inquiries::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}

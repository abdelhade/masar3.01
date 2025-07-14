<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StateController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض المحافظات')->only(['index']);
        $this->middleware('can:إضافة المحافظات')->only(['create', 'store']);
        $this->middleware('can:تعديل المحافظات')->only(['update', 'edit']);
        $this->middleware('can:حذف المحافظات')->only(['destroy']);
    }

    public function index()
    {
        return view('hr-management.addresses.manage-states');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

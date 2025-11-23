<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Contracts')->only(['index']);
        $this->middleware('can:create Contracts')->only(['create', 'store']);
        $this->middleware('can:edit Contracts')->only(['update', 'edit']);
        $this->middleware('can:delete Contracts')->only(['destroy']);
    }

    public function index()
    {
        return view('hr-management.contracts.contracts.manage-contracts');
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
    public function show(Contract $contract)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        //
    }
}

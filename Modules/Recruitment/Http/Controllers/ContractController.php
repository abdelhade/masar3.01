<?php

declare(strict_types=1);

namespace Modules\Recruitment\Http\Controllers;

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
        return view('recruitment::contracts.manage');
    }

    public function show($id)
    {
        $contract = \Modules\Recruitment\Models\Contract::with([
            'branch',
            'contract_type',
            'job',
            'employee',
            'user',
            'contract_points',
            'salary_points',
            'cv',
            'interview',
        ])->findOrFail($id);

        return view('recruitment::contracts.show', compact('contract'));
    }
}

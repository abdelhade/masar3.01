<?php

declare(strict_types=1);

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Routing\Controller;

class TerminationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Terminations')->only(['index']);
        $this->middleware('can:create Terminations')->only(['create', 'store']);
        $this->middleware('can:edit Terminations')->only(['update', 'edit']);
        $this->middleware('can:delete Terminations')->only(['destroy']);
    }

    public function index()
    {
        return view('recruitment::terminations.manage');
    }

    public function show($id)
    {
        $termination = \Modules\Recruitment\Models\Termination::with(['contract', 'branch'])->findOrFail($id);

        return view('recruitment::terminations.show', compact('termination'));
    }
}

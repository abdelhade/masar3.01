<?php

declare(strict_types=1);

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Routing\Controller;

class CvController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view CVs')->only(['index']);
        $this->middleware('can:create CVs')->only(['create', 'store']);
        $this->middleware('can:edit CVs')->only(['edit', 'update']);
        $this->middleware('can:delete CVs')->only(['destroy']);
    }

    public function index()
    {
        return view('recruitment::cvs.manage');
    }

    public function show($id)
    {
        $cv = \Modules\Recruitment\Models\Cv::with(['branch', 'jobPosting', 'interviews', 'contract'])->findOrFail($id);

        return view('recruitment::cvs.show', compact('cv'));
    }
}

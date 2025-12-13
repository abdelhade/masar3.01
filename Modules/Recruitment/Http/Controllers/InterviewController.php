<?php

declare(strict_types=1);

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Routing\Controller;

class InterviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Interviews')->only(['index']);
        $this->middleware('can:create Interviews')->only(['create', 'store']);
        $this->middleware('can:edit Interviews')->only(['update', 'edit']);
        $this->middleware('can:delete Interviews')->only(['destroy']);
    }

    public function index()
    {
        return view('recruitment::interviews.manage');
    }

    public function show($id)
    {
        $interview = \Modules\Recruitment\Models\Interview::with(['cv', 'branch'])->findOrFail($id);

        return view('recruitment::interviews.show', compact('interview'));
    }

    public function calendar()
    {
        return view('recruitment::interviews.calendar');
    }
}

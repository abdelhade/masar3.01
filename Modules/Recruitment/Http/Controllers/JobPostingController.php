<?php

declare(strict_types=1);

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Routing\Controller;

class JobPostingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Job Postings')->only(['index']);
        $this->middleware('can:create Job Postings')->only(['create', 'store']);
        $this->middleware('can:edit Job Postings')->only(['update', 'edit']);
        $this->middleware('can:delete Job Postings')->only(['destroy']);
    }

    public function index()
    {
        return view('recruitment::job-postings.manage');
    }

    public function show($id)
    {
        $jobPosting = \Modules\Recruitment\Models\JobPosting::with(['branch'])->findOrFail($id);

        return view('recruitment::job-postings.show', compact('jobPosting'));
    }
}

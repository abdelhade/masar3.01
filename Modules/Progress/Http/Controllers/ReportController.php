<?php
namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\Project;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view progress-reports');
    }

    public function progressReport()
    {
    $projects = Project::with(['items.workItem', 'items.dailyProgress'])->get();
        return view('progress::reports.progress', compact('projects'));
    }
}
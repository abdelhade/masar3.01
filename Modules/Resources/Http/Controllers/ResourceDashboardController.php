<?php

namespace Modules\MyResources\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceStatus;
use Modules\MyResources\Models\ResourceAssignment;

class ResourceDashboardController extends Controller
{
    public function index()
    {
        $totalResources = Resource::count();
        $activeResources = Resource::active()->count();
        
        $resourcesByCategory = Resource::selectRaw('resource_category_id, count(*) as count')
            ->with('category:id,name_ar')
            ->groupBy('resource_category_id')
            ->get();

        $resourcesByStatus = Resource::selectRaw('resource_status_id, count(*) as count')
            ->with('status:id,name_ar,color')
            ->groupBy('resource_status_id')
            ->get();

        $activeAssignments = ResourceAssignment::where('status', 'active')->count();
        $scheduledAssignments = ResourceAssignment::where('status', 'scheduled')->count();

        $upcomingMaintenance = Resource::whereNotNull('next_maintenance_date')
            ->whereDate('next_maintenance_date', '<=', now()->addDays(7))
            ->with(['category', 'type', 'status'])
            ->get();

        $recentAssignments = ResourceAssignment::with(['resource', 'project', 'assignedBy'])
            ->latest()
            ->limit(10)
            ->get();

        return view('myresources::dashboard.index', compact(
            'totalResources',
            'activeResources',
            'resourcesByCategory',
            'resourcesByStatus',
            'activeAssignments',
            'scheduledAssignments',
            'upcomingMaintenance',
            'recentAssignments'
        ));
    }
}


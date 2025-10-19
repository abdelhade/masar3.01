<?php

namespace App\Http\Controllers;

use App\Models\{AccHead, Project, OperHead};
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\{DB, Auth, Cache};

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:عرض المشاريع')->only(['index']);
        $this->middleware('can:إضافة المشاريع')->only(['create', 'store']);
        $this->middleware('can:تعديل المشاريع')->only(['update', 'edit']);
        $this->middleware('can:حذف العملاء')->only(['destroy']);
    }

    public function index()
    {
        return view('projects.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
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
    public function show($id)
    {
        $project = Project::findOrFail($id);

        $operations = OperHead::where('project_id', $id)->get();
        $equipments = AccHead::where('rent_to', $id)->get();

        // للحصول على عمليات المعدات لكل معدة
        $equipmentOperations = collect();
        foreach ($equipments as $equipment) {
            $operation = OperHead::where('acc3', $equipment->id)->orderBy('start_date')->first();
            if ($operation) {
                $equipmentOperations->push([
                    'equipment' => $equipment,
                    'operation' => $operation
                ]);
            }
        }

        $vouchers = OperHead::where('project_id', $id)
            ->where(function ($query) {
                $query->where('pro_type', 1)
                    ->orWhere('pro_type', 2);
            })
            ->get();

        return view('projects.show', compact('project', 'operations', 'equipments', 'vouchers', 'equipmentOperations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }

    public function statistics()
    {
        // خريطة حالات المشاريع
        $statusMap = [
            'pending' => ['title' => 'قيد الانتظار', 'color' => 'warning', 'icon' => 'la-hourglass-start'],
            'in_progress' => ['title' => 'قيد التنفيذ', 'color' => 'primary', 'icon' => 'la-cogs'],
            'completed' => ['title' => 'مكتمل', 'color' => 'success', 'icon' => 'la-check-circle'],
            'cancelled' => ['title' => 'ملغي', 'color' => 'danger', 'icon' => 'la-times-circle'],
        ];

        // Use Cache for 1 hour (TTL in seconds) and produce stable zero-filled stats
        $cacheKey = 'project_statistics_' . Auth::id(); // user-scoped cache key
        $statisticsData = Cache::remember($cacheKey, 60 * 60, function () use ($statusMap) {
            // إحصائيات حسب الحالة (عدد المشاريع ومتوسط المدة المتوقعة بالأيام)
            $raw = Project::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(DATEDIFF(end_date, start_date)) as avg_duration')
            )
                ->groupBy('status')
                ->get()
                ->keyBy('status')
                ->map(function ($item) use ($statusMap) {
                    return [
                        'title' => $statusMap[$item->status]['title'] ?? $item->status,
                        'count' => (int) $item->count,
                        'avg_duration' => $item->avg_duration !== null ? round($item->avg_duration, 1) : 0,
                        'color' => $statusMap[$item->status]['color'] ?? 'secondary',
                        'icon' => $statusMap[$item->status]['icon'] ?? 'la-project-diagram',
                    ];
                })->toArray();

            // Prepare defaults for all statuses so view and charts have stable data
            $defaults = [];
            foreach ($statusMap as $key => $cfg) {
                $defaults[$key] = [
                    'title' => $cfg['title'],
                    'count' => 0,
                    'avg_duration' => 0,
                    'color' => $cfg['color'] ?? 'secondary',
                    'icon' => $cfg['icon'] ?? 'la-project-diagram',
                ];
            }

            // Merge actual stats into defaults preserving order
            $sortedStatistics = array_replace($defaults, $raw);

            // إجمالي الكلي (عدد المشاريع)
            $overallTotal = Project::count();

            return compact('sortedStatistics', 'overallTotal');
        });

        // استخراج البيانات من الـ Cache
        $sortedStatistics = $statisticsData['sortedStatistics'];
        $overallTotal = $statisticsData['overallTotal'];

        return view('projects.statistics', compact('sortedStatistics', 'overallTotal'));
    }
}

<?php

namespace Modules\Manufacturing\Http\Controllers;

use App\Models\OperHead;
use Illuminate\Http\Request;
use App\Models\OperationItems;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;


class ManufacturingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض فاتورة تصنيع')->only(['index']);
        $this->middleware('can:إضافة فاتورة تصنيع')->only(['create', 'store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('manufacturing::manufacturing.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manufacturing::manufacturing.create');
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
        return view('manufacturing::manufacturing.edit', compact('id'));
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

    public function manufacturingStatistics()
    {

        // إجمالي عدد عمليات التصنيع
        $query = OperHead::where('pro_type', 59);
        $totalManufacturing = $query->count();

        // إجمالي تكلفة التصنيع
        $totalCost = $query->sum('pro_value');

        // متوسط تكلفة العملية
        $avgCost = $totalManufacturing > 0 ? round($totalCost / $totalManufacturing, 2) : 0;

        // أعلى وأقل تكلفة
        $maxCost = $query->max('pro_value') ?? 0;
        $minCost = $query->min('pro_value') ?? 0;

        // عمليات التصنيع خلال الشهر الحالي
        $currentMonthManufacturing = OperHead::where('pro_type', 59)
            ->whereYear('pro_date', date('Y'))
            ->whereMonth('pro_date', date('m'))
            ->count();

        $currentMonthCost = OperHead::where('pro_type', 59)
            ->whereYear('pro_date', date('Y'))
            ->whereMonth('pro_date', date('m'))
            ->sum('pro_value');

        // عمليات التصنيع خلال السنة الحالية
        $currentYearManufacturing = OperHead::where('pro_type', 59)
            ->whereYear('pro_date', date('Y'))
            ->count();

        $currentYearCost = OperHead::where('pro_type', 59)
            ->whereYear('pro_date', date('Y'))
            ->sum('pro_value');

        // أكثر 5 مواد خام استخدامًا (من OperationItems)
        $topRawMaterials = OperationItems::where('pro_tybe', 59)
            ->where('qty_out', '>', 0) // المواد الخام (qty_out > 0)
            ->selectRaw('item_id, COUNT(*) as count, SUM(detail_value) as total')
            ->groupBy('item_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with(['item:id,name'])
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->item->name ?? 'غير معروف',
                    'count' => $item->count,
                    'total' => $item->total
                ];
            });

        // التصنيع حسب الأشهر (آخر 6 أشهر)
        $monthlyManufacturing = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));

            $count = OperHead::where('pro_type', 59)
                ->whereYear('pro_date', $year)
                ->whereMonth('pro_date', $month)
                ->count();

            $value = OperHead::where('pro_type', 59)
                ->whereYear('pro_date', $year)
                ->whereMonth('pro_date', $month)
                ->sum('pro_value');

            $monthName = [
                '01' => 'يناير',
                '02' => 'فبراير',
                '03' => 'مارس',
                '04' => 'أبريل',
                '05' => 'مايو',
                '06' => 'يونيو',
                '07' => 'يوليو',
                '08' => 'أغسطس',
                '09' => 'سبتمبر',
                '10' => 'أكتوبر',
                '11' => 'نوفمبر',
                '12' => 'ديسمبر'
            ][$month] ?? '';

            $monthlyManufacturing[] = [
                'month' => date('M Y', strtotime($date)),
                'month_ar' => $monthName . ' ' . $year,
                'count' => $count,
                'value' => $value
            ];
        }

        // نطاقات التكاليف
        $costRanges = DB::table('operhead')
            ->where('pro_type', 59)
            ->select(
                DB::raw('CASE
            WHEN pro_value < 100 THEN "أقل من 100"
            WHEN pro_value >= 100 AND pro_value < 500 THEN "100 - 500"
            WHEN pro_value >= 500 AND pro_value < 1000 THEN "500 - 1000"
            WHEN pro_value >= 1000 AND pro_value < 5000 THEN "1000 - 5000"
            ELSE "أكثر من 5000"
        END as `range`'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(pro_value) as total')
            )
            ->groupBy(DB::raw('`range`'))
            ->get()
            ->map(function ($item) {
                return [
                    'range' => $item->range,
                    'count' => $item->count,
                    'total' => $item->total
                ];
            });


        // أحدث عمليات التصنيع
        $recentManufacturing = OperHead::where('pro_type', 59)
            ->with(['acc1Head:id,aname', 'acc2Head:id,aname'])
            ->orderByDesc('pro_date')
            ->limit(10)
            ->get()
            ->map(function ($operation) {
                return [
                    'id' => $operation->id,
                    'pro_id' => $operation->pro_id,
                    'account_name' => $operation->acc1Head->aname ?? $operation->acc2Head->aname ?? '-',
                    'value' => $operation->pro_value,
                    'date' => $operation->pro_date,
                    'info' => $operation->info ?? '-'
                ];
            });

        // إحصائيات حسب الفرع
        $branchStats = OperHead::where('pro_type', 59)
            ->select('branch_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(pro_value) as total'))
            ->groupBy('branch_id')
            ->with('branch:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'branch_name' => $item->branch->name ?? 'غير محدد',
                    'count' => $item->count,
                    'total' => $item->total
                ];
            });

        // مقارنة بين الشهر الحالي والشهر السابق
        $lastMonthManufacturing = OperHead::where('pro_type', 59)
            ->whereYear('pro_date', date('Y', strtotime('-1 month')))
            ->whereMonth('pro_date', date('m', strtotime('-1 month')))
            ->count();

        $lastMonthCost = OperHead::where('pro_type', 59)
            ->whereYear('pro_date', date('Y', strtotime('-1 month')))
            ->whereMonth('pro_date', date('m', strtotime('-1 month')))
            ->sum('pro_value');

        $countChange = $lastMonthManufacturing > 0
            ? round((($currentMonthManufacturing - $lastMonthManufacturing) / $lastMonthManufacturing) * 100, 2)
            : 0;

        $costChange = $lastMonthCost > 0
            ? round((($currentMonthCost - $lastMonthCost) / $lastMonthCost) * 100, 2)
            : 0;

        $statistics = compact(
            'totalManufacturing',
            'totalCost',
            'avgCost',
            'maxCost',
            'minCost',
            'currentMonthManufacturing',
            'currentMonthCost',
            'currentYearManufacturing',
            'currentYearCost',
            'topRawMaterials',
            'monthlyManufacturing',
            'costRanges',
            'recentManufacturing',
            'branchStats',
            'lastMonthManufacturing',
            'lastMonthCost',
            'countChange',
            'costChange'
        );

        return view('manufacturing::manufacturing.statistics', compact('statistics'));
    }
}

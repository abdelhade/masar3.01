<?php

namespace Modules\Inquiries\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Inquiries\Enums\{InquiryStatus, QuotationStateEnum};
use Modules\Inquiries\Models\{Inquiry, WorkType, InquirySource, ProjectSize, InquiryComment};

class InquiryStatisticsController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:view Inquiries')->only(['index']);
        $this->middleware('can:view Inquiries Statistics')->only(['index']);
    }

    public function index()
    {
        $stats = $this->getDashboardStatistics();
        return view('inquiries::dashboard.statistics', compact('stats'));
    }

    private function getDashboardStatistics(): array
    {
        return [
            'overview' => $this->getOverviewStats(),
            'status_breakdown' => $this->getStatusBreakdown(),
            'quotation_states' => $this->getQuotationStateBreakdown(),
            'work_types' => $this->getWorkTypeStats(),
            'sources' => $this->getSourceStats(),
            'sizes' => $this->getProjectSizeStats(),
            'monthly_trend' => $this->getMonthlyTrend(),
            'recent_inquiries' => $this->getRecentInquiries(),
            'comments_count' => InquiryComment::count(),
        ];
    }

    private function getOverviewStats(): array
    {
        return [
            'total' => Inquiry::count(),
            'active' => Inquiry::where('status', InquiryStatus::JOB_IN_HAND->value)->count(),
            'tender' => Inquiry::where('status', InquiryStatus::TENDER->value)->count(),
            'with_comments' => Inquiry::has('comments')->count(),
            'with_documents' => Inquiry::whereHas('projectDocuments')->count(),
            'with_work_types' => Inquiry::whereHas('workTypes')->count(),
        ];
    }

    private function getStatusBreakdown(): array
    {
        $counts = Inquiry::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [is_object($item->status) ? $item->status->value : (string)$item->status => (int)$item->count])
            ->toArray();
        $total = array_sum($counts);
        $statuses = InquiryStatus::cases();
        $breakdown = [];
        foreach ($statuses as $status) {
            $key = $status->value;
            $breakdown[$key] = [
                'label' => $status->label(),
                'count' => $counts[$key] ?? 0,
                'percentage' => $total > 0 ? round((($counts[$key] ?? 0) / $total) * 100, 2) : 0,
            ];
        }
        return $breakdown;
    }

    private function getQuotationStateBreakdown(): array
    {
        $counts = Inquiry::select('quotation_state', DB::raw('count(*) as count'))
            ->groupBy('quotation_state')
            ->get()
            ->mapWithKeys(fn($item) => [is_object($item->quotation_state) ? $item->quotation_state->value : (string)$item->quotation_state => (int)$item->count])
            ->toArray();
        $total = array_sum($counts);
        $states = QuotationStateEnum::cases();
        $breakdown = [];
        foreach ($states as $state) {
            $key = $state->value;
            $breakdown[$key] = [
                'label' => $state->label(),
                'color' => $state->color(),
                'count' => $counts[$key] ?? 0,
                'percentage' => $total > 0 ? round((($counts[$key] ?? 0) / $total) * 100, 2) : 0,
            ];
        }
        return $breakdown;
    }

    private function getWorkTypeStats(): array
    {
        $types = WorkType::withCount(['inquiries'])->orderByDesc('inquiries_count')->get();
        return $types->map(fn($type) => [
            'name' => $type->name,
            'count' => $type->inquiries_count,
        ])->toArray();
    }

    private function getSourceStats(): array
    {
        $sources = InquirySource::withCount(['inquiries'])->orderByDesc('inquiries_count')->get();
        return $sources->map(fn($src) => [
            'name' => $src->name,
            'count' => $src->inquiries_count,
        ])->toArray();
    }

    private function getProjectSizeStats(): array
    {
        $sizes = ProjectSize::withCount(['inquiries'])->orderByDesc('inquiries_count')->get();
        return $sizes->map(fn($sz) => [
            'name' => $sz->name,
            'count' => $sz->inquiries_count,
        ])->toArray();
    }

    private function getMonthlyTrend(): array
    {
        $months = Inquiry::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as month_name'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "' . InquiryStatus::JOB_IN_HAND->value . '" THEN 1 ELSE 0 END) as active'),
            DB::raw('SUM(CASE WHEN status = "' . InquiryStatus::TENDER->value . '" THEN 1 ELSE 0 END) as tender')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month', 'month_name')
            ->orderBy('month', 'asc')
            ->get();
        return [
            'labels' => $months->pluck('month_name')->toArray(),
            'data' => [
                'total' => $months->pluck('total')->map(fn($v) => (int)$v)->toArray(),
                'active' => $months->pluck('active')->map(fn($v) => (int)$v)->toArray(),
                'tender' => $months->pluck('tender')->map(fn($v) => (int)$v)->toArray(),
            ],
        ];
    }

    private function getRecentInquiries(): array
    {
        return Inquiry::with(['clients', 'workTypes', 'inquirySource', 'projectSize'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($inq) {
                return [
                    'id' => $inq->id,
                    'clients' => optional($inq->clients->first())->name,
                    'status' => $inq->status?->label() ?? '-',
                    'work_types' => $inq->workTypes->pluck('name')->implode(', '),
                    'source' => $inq->inquirySource?->name,
                    'size' => $inq->projectSize?->name,
                    'created_at' => $inq->created_at?->format('Y-m-d'),
                ];
            })->toArray();
    }
}

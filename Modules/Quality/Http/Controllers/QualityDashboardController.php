<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\QualityInspection;
use Modules\Quality\Models\NonConformanceReport;
use Modules\Quality\Models\CorrectiveAction;
use Modules\Quality\Models\BatchTracking;
use Modules\Quality\Models\QualityCertificate;
use Modules\Quality\Models\SupplierRating;

class QualityDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view quality')->only(['index']);


    }
    public function index()
    {
        // Quality Metrics
        $totalInspections = QualityInspection::count();
        $passedInspections = QualityInspection::where('result', 'pass')->count();
        $failedInspections = QualityInspection::where('result', 'fail')->count();
        $passRate = $totalInspections > 0 ? ($passedInspections / $totalInspections) * 100 : 0;

        // NCR Metrics
        $openNCRs = NonConformanceReport::where('status', 'open')->count();
        $criticalNCRs = NonConformanceReport::where('severity', 'critical')->count();
        $totalNCRs = NonConformanceReport::count();

        // CAPA Metrics
        $activeCapas = CorrectiveAction::whereIn('status', ['planned', 'in_progress'])->count();
        $overdueCapas = CorrectiveAction::overdue()->count();

        // Batch Tracking
        $activeBatches = BatchTracking::where('status', 'active')->count();
        $expiringSoonBatches = BatchTracking::expiringSoon(30)->count();
        $expiredBatches = BatchTracking::expired()->count();

        // Certificates
        $activeCertificates = QualityCertificate::active()->count();
        $expiringCertificates = QualityCertificate::expiringSoon()->count();

        // Recent Inspections
        $recentInspections = QualityInspection::with(['item', 'inspector'])
            ->orderBy('inspection_date', 'desc')
            ->limit(10)
            ->get();

        // Recent NCRs
        $recentNCRs = NonConformanceReport::with(['item', 'detectedBy'])
            ->orderBy('detected_date', 'desc')
            ->limit(10)
            ->get();

        // Top Suppliers
        $topSuppliers = SupplierRating::with('supplier')
            ->where('rating', 'excellent')
            ->orderBy('overall_score', 'desc')
            ->limit(5)
            ->get();

        return view('quality::dashboard.index', compact(
            'totalInspections',
            'passedInspections',
            'failedInspections',
            'passRate',
            'openNCRs',
            'criticalNCRs',
            'totalNCRs',
            'activeCapas',
            'overdueCapas',
            'activeBatches',
            'expiringSoonBatches',
            'expiredBatches',
            'activeCertificates',
            'expiringCertificates',
            'recentInspections',
            'recentNCRs',
            'topSuppliers'
        ));
    }
}


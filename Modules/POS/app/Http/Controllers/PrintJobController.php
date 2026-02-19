<?php

declare(strict_types=1);

namespace Modules\POS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\POS\Jobs\PrintKitchenOrderJob;
use Modules\POS\Models\KitchenPrinterStation;
use Modules\POS\Models\PrintJob;
use RealRashid\SweetAlert\Facades\Alert;

class PrintJobController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Print Jobs')->only(['index']);
        $this->middleware('permission:retry Print Jobs')->only(['retry']);
    }

    public function index(Request $request)
    {
        $query = PrintJob::with(['printerStation', 'transaction', 'printedBy'])
            ->orderBy('created_at', 'desc');

        // تصفية حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // تصفية حسب المحطة
        if ($request->filled('printer_station_id')) {
            $query->where('printer_station_id', $request->printer_station_id);
        }

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $printJobs = $query->paginate(50);
        $printerStations = KitchenPrinterStation::orderBy('name')->get();

        return view('pos::print-jobs.index', compact('printJobs', 'printerStations'));
    }

    public function retry(PrintJob $printJob)
    {
        try {
            if (! $printJob->transaction) {
                Alert::error(__('pos.transaction_not_found'));

                return back();
            }

            $printJob->markAsRetrying();

            PrintKitchenOrderJob::dispatch(
                $printJob->transaction,
                $printJob->printerStation,
                true,
                auth()->id()
            );

            Alert::success(__('pos.print_job_queued_for_retry'));
        } catch (\Exception $e) {
            Alert::error(__('pos.print_job_retry_failed'));
        }

        return back();
    }
}

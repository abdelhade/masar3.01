<?php

namespace Modules\Checks\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Checks\Models\Check;
use Modules\Checks\Services\CheckService;

class CheckExportController extends Controller
{
    public function __construct(
        private CheckService $checkService
    ) {}

    /**
     * Export checks to PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'per_page' => 10000,
        ];

        $checks = $this->checkService->getChecks($filters)->items();

        $data = [
            'checks' => $checks,
            'filters' => $filters,
            'total_amount' => collect($checks)->sum('amount'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];

        $pdf = Pdf::loadView('checks::exports.pdf', $data);

        return $pdf->download('checks_export_'.now()->format('Y_m_d_H_i_s').'.pdf');
    }

    /**
     * Export checks to Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'per_page' => 10000,
        ];

        $checks = $this->checkService->getChecks($filters)->items();

        $filename = 'checks_export_'.now()->format('Y_m_d_H_i_s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($checks) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for proper Arabic encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV headers
            fputcsv($file, [
                'رقم الشيك',
                'البنك',
                'رقم الحساب',
                'صاحب الحساب',
                'المبلغ',
                'تاريخ الإصدار',
                'تاريخ الاستحقاق',
                'تاريخ الدفع',
                'الحالة',
                'النوع',
                'المستفيد',
                'الدافع',
                'رقم المرجع',
                'ملاحظات',
                'أنشئ بواسطة',
                'تاريخ الإنشاء',
            ]);

            // Data rows
            foreach ($checks as $check) {
                fputcsv($file, [
                    $check->check_number,
                    $check->bank_name,
                    $check->account_number,
                    $check->account_holder_name,
                    $check->amount,
                    $check->issue_date->format('Y-m-d'),
                    $check->due_date->format('Y-m-d'),
                    $check->payment_date ? $check->payment_date->format('Y-m-d') : '',
                    Check::getStatuses()[$check->status] ?? $check->status,
                    Check::getTypes()[$check->type] ?? $check->type,
                    $check->payee_name,
                    $check->payer_name,
                    $check->reference_number,
                    $check->notes,
                    $check->creator->name ?? '',
                    $check->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

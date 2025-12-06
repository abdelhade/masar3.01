<?php

declare(strict_types=1);

namespace App\Livewire\FlexibleSalaryProcessing;

use App\Models\FlexibleSalaryProcessing;
use App\Services\JournalService;
use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function approveProcessing(int $processingId): void
    {
        DB::beginTransaction();

        try {
            $processing = FlexibleSalaryProcessing::with('employee')->findOrFail($processingId);

            if ($processing->status === 'approved') {
                session()->flash('error', 'المعالجة معتمدة بالفعل');
                DB::rollBack();

                return;
            }

            $employee = $processing->employee;

            if (! $employee->account) {
                throw new \Exception('حساب الموظف غير موجود');
            }

            $debitAccount = AccHead::where('code', '5301')->first();
            if (! $debitAccount) {
                throw new \Exception('حساب رواتب الموظفين (5301) غير موجود');
            }

            $journalService = app(JournalService::class);

            $lines = [
                [
                    'account_id' => $debitAccount->id,
                    'debit' => $processing->total_salary,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "راتب مرن للموظف: {$employee->name} - معالجة #{$processingId}",
                ],
                [
                    'account_id' => $employee->account->id,
                    'debit' => 0,
                    'credit' => $processing->total_salary,
                    'type' => 0,
                    'info' => "راتب مرن للموظف: {$employee->name} - معالجة #{$processingId}",
                ],
            ];

            $meta = [
                'pro_type' => 77,
                'date' => now(),
                'info' => "راتب مرن للموظف: {$employee->name}",
                'details' => "معالجة الراتب المرن #{$processingId} - الفترة من {$processing->period_start->format('Y-m-d')} إلى {$processing->period_end->format('Y-m-d')}",
                'emp_id' => $employee->id,
            ];

            $journalId = $journalService->createJournal($lines, $meta);

            $processing->status = 'approved';
            $processing->journal_id = $journalId;
            $processing->save();

            DB::commit();
            $this->resetPage();
            session()->flash('success', 'تم الموافقة على المعالجة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving flexible salary processing: '.$e->getMessage(), [
                'processing_id' => $processingId,
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء الموافقة: '.$e->getMessage());
        }
    }

    public function rejectProcessing(int $processingId): void
    {
        try {
            $processing = FlexibleSalaryProcessing::findOrFail($processingId);
            $processing->status = 'rejected';
            $processing->save();

            $this->resetPage();
            session()->flash('success', 'تم رفض المعالجة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء الرفض: '.$e->getMessage());
        }
    }

    public function getProcessingsProperty()
    {
        return FlexibleSalaryProcessing::with(['employee', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.flexible-salary-processing.index', [
            'processings' => $this->processings,
        ]);
    }
}


<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OperationConstraintsButton extends Component
{
    public int $operheadId;

    public $journalHeads = [];

    public function mount(int $operheadId): void
    {
        $this->operheadId = $operheadId;
    }

    public function openModal(): void
    {
        $operhead = DB::table('operhead')
            ->where('id', $this->operheadId)
            ->where('is_journal', 1)
            ->where('isdeleted', 0)
            ->first();

        if (! $operhead) {
            return;
        }

        // جلب القيود من journal_heads
        $this->journalHeads = DB::table('journal_heads')
            ->where('isdeleted', 0)
            ->where(function ($query) use ($operhead) {
                $query->where('op_id', $operhead->id)
                    ->orWhere('op2', $operhead->id)
                    ->orWhere(function ($q) use ($operhead) {
                        if ($operhead->op2) {
                            $q->where('op_id', $operhead->op2)
                                ->orWhere('op2', $operhead->op2);
                        }
                    });
            })
            ->get()
            ->map(function ($head) {
                // جلب تفاصيل كل قيد مع اسم الحساب
                $head->details = DB::table('journal_details')
                    ->leftJoin('acc_head', 'journal_details.account_id', '=', 'acc_head.id')
                    ->select(
                        'journal_details.*',
                        'acc_head.aname as account_name'
                    )
                    ->where('journal_details.journal_id', $head->id)
                    ->where('journal_details.isdeleted', 0)
                    ->get()
                    ->map(function ($detail) {
                        // إذا لم يتم العثور على اسم الحساب، نحاول جلبه من جدول آخر أو نعرض رقم الحساب
                        if (!$detail->account_name) {
                            $detail->account_name = 'حساب #' . $detail->account_id;
                        }
                        return $detail;
                    });

                return $head;
            });

        $this->modal('operation-constraints-'.$this->operheadId)->show();
    }

    public function closeModal(): void
    {
        $this->modal('operation-constraints-'.$this->operheadId)->close();
        $this->journalHeads = [];
    }

    public function render()
    {
        return view('livewire.operation-constraints-button');
    }
}

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
                // جلب تفاصيل كل قيد (journal_id في journal_details يشير إلى journal_heads.id)
                $head->details = DB::table('journal_details')
                    ->where('journal_id', $head->id)
                    ->where('isdeleted', 0)
                    ->get();

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

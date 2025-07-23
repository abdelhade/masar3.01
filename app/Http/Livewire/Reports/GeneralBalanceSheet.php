<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\AccHead;
use Carbon\Carbon;

class GeneralBalanceSheet extends Component
{
    public $asOfDate;
    public $assets = [];
    public $liabilities = [];
    public $equity = [];
    public $totalAssets = 0;
    public $totalLiabilitiesEquity = 0;

    public function mount()
    {
        $this->asOfDate = now()->format('Y-m-d');
        $this->generateReport();
    }

    public function updatedAsOfDate()
    {
        $this->generateReport();
    }

    public function generateReport()
    {
        $date = $this->asOfDate;
        // مثال: جلب الحسابات حسب نوعها (يجب تعديلها حسب بنية قاعدة البيانات لديك)
        $this->assets = AccHead::where('type', 'asset')->get();
        $this->liabilities = AccHead::where('type', 'liability')->get();
        $this->equity = AccHead::where('type', 'equity')->get();

        $this->totalAssets = $this->assets->sum('balance');
        $this->totalLiabilitiesEquity = $this->liabilities->sum('balance') + $this->equity->sum('balance');
    }

    public function render()
    {
        return view('livewire.reports.general-balance-sheet');
    }
} 
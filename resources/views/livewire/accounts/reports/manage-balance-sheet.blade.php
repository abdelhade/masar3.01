<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use Carbon\Carbon;
use Modules\Settings\Models\PublicSetting;

new class extends Component { 
    public $balanceSheetDate;
    public $companyName = '';
    public $totalAssets = 0;
    public $totalLiabilities = 0;
    public $totalEquity = 0;

    // Balance Sheet Data
    public $currentAssets = [];
    public $nonCurrentAssets = [];
    public $currentLiabilities = [];
    public $nonCurrentLiabilities = [];
    public $equity = [];

    // Totals
    public $currentAssetsTotal = 0;
    public $nonCurrentAssetsTotal = 0;
    public $currentLiabilitiesTotal = 0;
    public $nonCurrentLiabilitiesTotal = 0;
    public $equityTotal = 0;

    public function mount()
    {
        $this->balanceSheetDate =  PublicSetting::where('key', 'start_date')->value('value') ?? now()->toDateString();
        $this->loadBalanceSheetData();
    }

    public function loadBalanceSheetData()
    {
        // Load Current Assets (الأصول المتداولة)
        $this->currentAssets = $this->getCurrentAssets();
        $this->currentAssetsTotal = collect($this->currentAssets)->sum('balance');

        // Load Non-Current Assets (الأصول غير المتداولة)
        $this->nonCurrentAssets = $this->getNonCurrentAssets();
        $this->nonCurrentAssetsTotal = collect($this->nonCurrentAssets)->sum('balance');

        // Load Current Liabilities (الخصوم المتداولة)
        $this->currentLiabilities = $this->getCurrentLiabilities();
        $this->currentLiabilitiesTotal = collect($this->currentLiabilities)->sum('balance');

        // Load Non-Current Liabilities (الخصوم غير المتداولة)
        $this->nonCurrentLiabilities = $this->getNonCurrentLiabilities();
        $this->nonCurrentLiabilitiesTotal = collect($this->nonCurrentLiabilities)->sum('balance');

        // Load Equity (حقوق الملكية)
        $this->equity = $this->getEquity();
        $this->equityTotal = collect($this->equity)->sum('balance');

        // Calculate totals
        $this->totalAssets = $this->currentAssetsTotal + $this->nonCurrentAssetsTotal;
        $this->totalLiabilities = $this->currentLiabilitiesTotal + $this->nonCurrentLiabilitiesTotal;
        $this->totalEquity = $this->equityTotal;
    }

    private function getCurrentAssets()
    {
        return AccHead::where('is_basic', 0)
            ->where('code', 'like', '12%') // الأصول المتداولة
            ->where('isdeleted', 0)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->aname,
                    'balance' => $this->calculateAccountBalance($account->id),
                    'category' => $this->getCurrentAssetCategory($account->code),
                ];
            })
            ->filter(function ($account) {
                return $account['balance'] != 0;
            })
            ->sortBy('code')
            ->values()
            ->toArray();
    }

    private function getNonCurrentAssets()
    {
        return AccHead::where('is_basic', 0)
            ->where('code', 'like', '11%') // الأصول الثابتة
            ->where('isdeleted', 0)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->aname,
                    'balance' => $this->calculateAccountBalance($account->id),
                    'category' => $this->getNonCurrentAssetCategory($account->code),
                ];
            })
            ->filter(function ($account) {
                return $account['balance'] != 0;
            })
            ->sortBy('code')
            ->values()
            ->toArray();
    }

    private function getCurrentLiabilities()
    {
        return AccHead::where('is_basic', 0)
            ->where('code', 'like', '21%') // الخصوم المتداولة
            ->where('isdeleted', 0)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->aname,
                    'balance' => abs($this->calculateAccountBalance($account->id)),
                    'category' => $this->getCurrentLiabilityCategory($account->code),
                ];
            })
            ->filter(function ($account) {
                return $account['balance'] != 0;
            })
            ->sortBy('code')
            ->values()
            ->toArray();
    }

    private function getNonCurrentLiabilities()
    {
        return AccHead::where('is_basic', 0)
            ->where('code', 'like', '22%') // الخصوم غير المتداولة
            ->where('isdeleted', 0)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->aname,
                    'balance' => abs($this->calculateAccountBalance($account->id)),
                    'category' => $this->getNonCurrentLiabilityCategory($account->code),
                ];
            })
            ->filter(function ($account) {
                return $account['balance'] != 0;
            })
            ->sortBy('code')
            ->values()
            ->toArray();
    }

    private function getEquity()
    {
        return AccHead::where('is_basic', 0)
            ->where('code', 'like', '23%') // حقوق الملكية
            ->where('isdeleted', 0)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->aname,
                    'balance' => abs($this->calculateAccountBalance($account->id)),
                    'category' => $this->getEquityCategory($account->code),
                ];
            })
            ->filter(function ($account) {
                return $account['balance'] != 0;
            })
            ->sortBy('code')
            ->values()
            ->toArray();
    }

    private function calculateAccountBalance($accountId)
    {
        $account = AccHead::find($accountId);
        if (!$account) {
            return 0;
        }

        // Get start balance
        // $startBalance = $account->start_balance ?? 0;

        // Get transaction balance
        $totalDebit = JournalDetail::where('account_id', $accountId)->where('isdeleted', 0)->sum('debit');

        $totalCredit = JournalDetail::where('account_id', $accountId)->where('isdeleted', 0)->sum('credit');

        return $totalDebit - $totalCredit;
        // return $startBalance + $totalDebit - $totalCredit;
    }

    private function getCurrentAssetCategory($code)
    {
        if (str_starts_with($code, '121')) {
            return 'النقد وما يعادله';
        }
        if (str_starts_with($code, '122')) {
            return 'المدينون والتأمينات';
        }
        if (str_starts_with($code, '123')) {
            return 'مخزون البضاعة';
        }
        if (str_starts_with($code, '124')) {
            return 'الاستثمارات قصيرة الأجل';
        }
        if (str_starts_with($code, '125')) {
            return 'مصروفات مدفوعة مقدماً';
        }
        return 'أصول متداولة أخرى';
    }

    private function getNonCurrentAssetCategory($code)
    {
        if (str_starts_with($code, '111')) {
            return 'الممتلكات والآلات والمعدات';
        }
        if (str_starts_with($code, '112')) {
            return 'الاستثمارات طويلة الأجل';
        }
        if (str_starts_with($code, '113')) {
            return 'الأصول غير الملموسة';
        }
        if (str_starts_with($code, '114')) {
            return 'الإهلاك والاستنزاف المتراكم';
        }
        return 'أصول غير متداولة أخرى';
    }

    private function getCurrentLiabilityCategory($code)
    {
        if (str_starts_with($code, '211')) {
            return 'الدائنون';
        }
        if (str_starts_with($code, '212')) {
            return 'قروض قصيرة الأجل';
        }
        if (str_starts_with($code, '213')) {
            return 'ضرائب مستحقة';
        }
        return 'التزامات قصيرة الأجل أخرى';
    }

    private function getNonCurrentLiabilityCategory($code)
    {
        if (str_starts_with($code, '221')) {
            return 'قروض طويلة الأجل';
        }
        if (str_starts_with($code, '222')) {
            return 'التزامات ضريبية مؤجلة';
        }
        if (str_starts_with($code, '223')) {
            return 'التزامات معاشات الموظفين';
        }
        return 'التزامات طويلة الأجل أخرى';
    }

    private function getEquityCategory($code)
    {
        if (str_starts_with($code, '231')) {
            return 'رأس المال';
        }
        if (str_starts_with($code, '232')) {
            return 'أرباح محتجزة';
        }
        if (str_starts_with($code, '233')) {
            return 'احتياطيات أخرى';
        }
        return 'حقوق الملكية';
    }

    public function refreshBalanceSheet()
    {
        $this->loadBalanceSheetData();
    }

    public function exportBalanceSheet()
    {
        // Implementation for exporting balance sheet
        // This can be implemented later
    }
}; ?>

<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title font-family-cairo fw-bold">الميزانية العمومية</h4>
            </div>
        </div>
    </div>

    <!-- Balance Sheet Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-family-cairo fw-bold">اسم الشركة:</label>
                                <input type="text" wire:model="companyName" class="form-control"
                                    placeholder="أدخل اسم الشركة">
                            </div>
                        </div>
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-family-cairo fw-bold">تاريخ الميزانية العمومية:</label>
                                <input type="date" wire:model="balanceSheetDate" class="form-control">
                            </div>
                        </div> --}}
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button wire:click="refreshBalanceSheet" class="btn btn-main font-family-cairo">
                                <i class="fas fa-sync-alt"></i> تحديث الميزانية
                            </button>
                            <button wire:click="exportBalanceSheet" class="btn btn-success font-family-cairo ms-2">
                                <i class="fas fa-download"></i> تصدير الميزانية
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Sheet Content -->
    <div class="row">
        <div class="col-9 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title font-family-cairo fw-bold">الميزانية العمومية -
                        {{ $companyName ?: 'اسم الشركة' }}</h5>
                    <p class="text-muted font-family-cairo">تاريخ الميزانية:
                        {{ \Carbon\Carbon::parse($balanceSheetDate)->format('Y-m-d') }}</p>
                    <p class="text-muted font-family-cairo">(جميع المبالغ بالعملة المحلية)</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="font-family-cairo fw-bold text-center text-white" style="width: 60%">البند</th>
                                    <th class="font-family-cairo fw-bold text-center text-white" style="width: 40%">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- الأصول (Assets) -->
                                <tr class="table-primary">
                                    <td colspan="2" class="font-family-cairo fw-bold fs-5">الأصول (Assets)</td>
                                </tr>
                                
                                <!-- الأصول المتداولة -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">الأصول المتداولة (Current Assets)</td>
                                    <td></td>
                                </tr>
                                
                                @foreach ($currentAssets as $asset)
                                    <tr>
                                        <td class="font-family-cairo fw-bold" style="padding-right: 30px;">{{ $asset['name'] }}
                                        </td>
                                        <td class="text-end font-family-cairo fw-bold">{{ number_format($asset['balance'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <tr class="table-warning">
                                    <td class="font-family-cairo fw-bold">إجمالي الأصول المتداولة</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($currentAssetsTotal, 2) }}</td>
                                </tr>
                                
                                <!-- الأصول غير المتداولة -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">الأصول غير المتداولة (Non-Current Assets)</td>
                                    <td></td>
                                </tr>
                                
                                @foreach ($nonCurrentAssets as $asset)
                                    <tr>
                                        <td class="font-family-cairo" style="padding-right: 30px;">{{ $asset['name'] }}
                                        </td>
                                        <td class="text-end font-family-cairo">{{ number_format($asset['balance'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <tr class="table-warning">
                                    <td class="font-family-cairo fw-bold">إجمالي الأصول غير المتداولة</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($nonCurrentAssetsTotal, 2) }}</td>
                                </tr>
                                
                                <tr class="table-success">
                                    <td class="font-family-cairo fw-bold fs-5">إجمالي الأصول</td>
                                    <td class="text-end font-family-cairo fw-bold fs-5">
                                        {{ number_format($totalAssets, 2) }}</td>
                                </tr>
                                
                                <!-- الخصوم وحقوق الملكية -->
                                <tr class="table-primary">
                                    <td colspan="2" class="font-family-cairo fw-bold fs-5">الخصوم وحقوق الملكية
                                        (Liabilities & Equity)</td>
                                </tr>
                                
                                <!-- الخصوم المتداولة -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">الخصوم المتداولة (Current Liabilities)</td>
                                    <td></td>
                                </tr>
                                
                                @foreach ($currentLiabilities as $liability)
                                    <tr>
                                        <td class="font-family-cairo fw-bold" style="padding-right: 30px;">
                                            {{ $liability['name'] }}</td>
                                        <td class="text-end font-family-cairo fw-bold">
                                            {{ number_format($liability['balance'], 2) }}</td>
                                    </tr>
                                @endforeach
                                
                                <tr class="table-warning">
                                    <td class="font-family-cairo fw-bold">إجمالي الخصوم المتداولة</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($currentLiabilitiesTotal, 2) }}</td>
                                </tr>
                                
                                <!-- الخصوم غير المتداولة -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">الخصوم غير المتداولة (Non-Current Liabilities)
                                    </td>
                                    <td></td>
                                </tr>
                                
                                @foreach ($nonCurrentLiabilities as $liability)
                                    <tr>
                                        <td class="font-family-cairo fw-bold" style="padding-right: 30px;">
                                            {{ $liability['name'] }}</td>
                                        <td class="text-end font-family-cairo fw-bold">
                                            {{ number_format($liability['balance'], 2) }}</td>
                                    </tr>
                                @endforeach
                                
                                <tr class="table-warning">
                                    <td class="font-family-cairo fw-bold">إجمالي الخصوم غير المتداولة</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($nonCurrentLiabilitiesTotal, 2) }}</td>
                                </tr>
                                
                                <tr class="table-warning">
                                    <td class="font-family-cairo fw-bold">إجمالي الخصوم</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($totalLiabilities, 2) }}</td>
                                </tr>
                                
                                <!-- حقوق الملكية -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">حقوق الملكية (Equity)</td>
                                    <td></td>
                                </tr>
                                
                                @foreach ($equity as $equityItem)
                                    <tr>
                                        <td class="font-family-cairo fw-bold" style="padding-right: 30px;">
                                            {{ $equityItem['name'] }}</td>
                                        <td class="text-end font-family-cairo fw-bold">
                                            {{ number_format($equityItem['balance'], 2) }}</td>
                                    </tr>
                                @endforeach
                                
                                <tr class="table-warning">
                                    <td class="font-family-cairo fw-bold">إجمالي حقوق الملكية</td>
                                    <td class="text-end font-family-cairo fw-bold">{{ number_format($totalEquity, 2) }}
                                    </td>
                                </tr>
                                
                                <tr class="table-success">
                                    <td class="font-family-cairo fw-bold fs-5">إجمالي الخصوم وحقوق الملكية</td>
                                    <td class="text-end font-family-cairo fw-bold fs-5">
                                        {{ number_format($totalLiabilities + $totalEquity, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Balance Sheet Equation Check -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div
                                class="alert {{ $totalAssets == $totalLiabilities + $totalEquity ? 'alert-success' : 'alert-danger' }}">
                                <h6 class="font-family-cairo fw-bold">
                                    معادلة الميزانية العمومية:
                                    إجمالي الأصول ({{ number_format($totalAssets, 2) }})
                                    {{ $totalAssets == $totalLiabilities + $totalEquity ? '=' : '≠' }}
                                    إجمالي الخصوم + حقوق الملكية
                                    ({{ number_format($totalLiabilities + $totalEquity, 2) }})
                                </h6>
                                @if ($totalAssets != $totalLiabilities + $totalEquity)
                                    <p class="font-family-cairo text-danger">
                                        تحذير: الميزانية غير متوازنة. الفرق:
                                        {{ number_format($totalAssets - ($totalLiabilities + $totalEquity), 2) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .font-family-cairo {
        font-family: 'Cairo', sans-serif;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    .text-end {
        text-align: end !important;
    }

    .table-responsive {
        direction: rtl;
    }

    .table {
        direction: rtl;
    }

    .table th,
    .table td {
        text-align: right;
    }

    .table th.text-center,
    .table td.text-center {
        text-align: center !important;
    }

    .table th.text-end,
    .table td.text-end {
        text-align: left !important;
    }
    </style>
</div>

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
        // Load Current Assets (Ø§Ù„Ø£ØµÙˆÙ„ Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©)
        $this->currentAssets = $this->getCurrentAssets();
        $this->currentAssetsTotal = collect($this->currentAssets)->sum('balance');

        // Load Non-Current Assets (Ø§Ù„Ø£ØµÙˆÙ„ ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©)
        $this->nonCurrentAssets = $this->getNonCurrentAssets();
        $this->nonCurrentAssetsTotal = collect($this->nonCurrentAssets)->sum('balance');

        // Load Current Liabilities (Ø§Ù„Ø®ØµÙˆÙ… Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©)
        $this->currentLiabilities = $this->getCurrentLiabilities();
        $this->currentLiabilitiesTotal = collect($this->currentLiabilities)->sum('balance');

        // Load Non-Current Liabilities (Ø§Ù„Ø®ØµÙˆÙ… ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©)
        $this->nonCurrentLiabilities = $this->getNonCurrentLiabilities();
        $this->nonCurrentLiabilitiesTotal = collect($this->nonCurrentLiabilities)->sum('balance');

        // Load Equity (Ø­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ©)
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
            ->where('code', 'like', '12%') // Ø§Ù„Ø£ØµÙˆÙ„ Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©
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
            ->where('code', 'like', '11%') // Ø§Ù„Ø£ØµÙˆÙ„ Ø§Ù„Ø«Ø§Ø¨ØªØ©
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
            ->where('code', 'like', '21%') // Ø§Ù„Ø®ØµÙˆÙ… Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©
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
            ->where('code', 'like', '22%') // Ø§Ù„Ø®ØµÙˆÙ… ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©
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
            ->where('code', 'like', '23%') // Ø­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ©
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
            return 'Ø§Ù„Ù†Ù‚Ø¯ ÙˆÙ…Ø§ ÙŠØ¹Ø§Ø¯Ù„Ù‡';
        }
        if (str_starts_with($code, '122')) {
            return 'Ø§Ù„Ù…Ø¯ÙŠÙ†ÙˆÙ† ÙˆØ§Ù„ØªØ£Ù…ÙŠÙ†Ø§Øª';
        }
        if (str_starts_with($code, '123')) {
            return 'Ù…Ø®Ø²ÙˆÙ† Ø§Ù„Ø¨Ø¶Ø§Ø¹Ø©';
        }
        if (str_starts_with($code, '124')) {
            return 'Ø§Ù„Ø§Ø³ØªØ«Ù…Ø§Ø±Ø§Øª Ù‚ØµÙŠØ±Ø© Ø§Ù„Ø£Ø¬Ù„';
        }
        if (str_starts_with($code, '125')) {
            return 'Ù…ØµØ±ÙˆÙØ§Øª Ù…Ø¯ÙÙˆØ¹Ø© Ù…Ù‚Ø¯Ù…Ø§Ù‹';
        }
        return 'Ø£ØµÙˆÙ„ Ù…ØªØ¯Ø§ÙˆÙ„Ø© Ø£Ø®Ø±Ù‰';
    }

    private function getNonCurrentAssetCategory($code)
    {
        if (str_starts_with($code, '111')) {
            return 'Ø§Ù„Ù…Ù…ØªÙ„ÙƒØ§Øª ÙˆØ§Ù„Ø¢Ù„Ø§Øª ÙˆØ§Ù„Ù…Ø¹Ø¯Ø§Øª';
        }
        if (str_starts_with($code, '112')) {
            return 'Ø§Ù„Ø§Ø³ØªØ«Ù…Ø§Ø±Ø§Øª Ø·ÙˆÙŠÙ„Ø© Ø§Ù„Ø£Ø¬Ù„';
        }
        if (str_starts_with($code, '113')) {
            return 'Ø§Ù„Ø£ØµÙˆÙ„ ØºÙŠØ± Ø§Ù„Ù…Ù„Ù…ÙˆØ³Ø©';
        }
        if (str_starts_with($code, '114')) {
            return 'Ø§Ù„Ø¥Ù‡Ù„Ø§Ùƒ ÙˆØ§Ù„Ø§Ø³ØªÙ†Ø²Ø§Ù Ø§Ù„Ù…ØªØ±Ø§ÙƒÙ…';
        }
        return 'Ø£ØµÙˆÙ„ ØºÙŠØ± Ù…ØªØ¯Ø§ÙˆÙ„Ø© Ø£Ø®Ø±Ù‰';
    }

    private function getCurrentLiabilityCategory($code)
    {
        if (str_starts_with($code, '211')) {
            return 'Ø§Ù„Ø¯Ø§Ø¦Ù†ÙˆÙ†';
        }
        if (str_starts_with($code, '212')) {
            return 'Ù‚Ø±ÙˆØ¶ Ù‚ØµÙŠØ±Ø© Ø§Ù„Ø£Ø¬Ù„';
        }
        if (str_starts_with($code, '213')) {
            return 'Ø¶Ø±Ø§Ø¦Ø¨ Ù…Ø³ØªØ­Ù‚Ø©';
        }
        return 'Ø§Ù„ØªØ²Ø§Ù…Ø§Øª Ù‚ØµÙŠØ±Ø© Ø§Ù„Ø£Ø¬Ù„ Ø£Ø®Ø±Ù‰';
    }

    private function getNonCurrentLiabilityCategory($code)
    {
        if (str_starts_with($code, '221')) {
            return 'Ù‚Ø±ÙˆØ¶ Ø·ÙˆÙŠÙ„Ø© Ø§Ù„Ø£Ø¬Ù„';
        }
        if (str_starts_with($code, '222')) {
            return 'Ø§Ù„ØªØ²Ø§Ù…Ø§Øª Ø¶Ø±ÙŠØ¨ÙŠØ© Ù…Ø¤Ø¬Ù„Ø©';
        }
        if (str_starts_with($code, '223')) {
            return 'Ø§Ù„ØªØ²Ø§Ù…Ø§Øª Ù…Ø¹Ø§Ø´Ø§Øª Ø§Ù„Ù…ÙˆØ¸ÙÙŠÙ†';
        }
        return 'Ø§Ù„ØªØ²Ø§Ù…Ø§Øª Ø·ÙˆÙŠÙ„Ø© Ø§Ù„Ø£Ø¬Ù„ Ø£Ø®Ø±Ù‰';
    }

    private function getEquityCategory($code)
    {
        if (str_starts_with($code, '231')) {
            return 'Ø±Ø£Ø³ Ø§Ù„Ù…Ø§Ù„';
        }
        if (str_starts_with($code, '232')) {
            return 'Ø£Ø±Ø¨Ø§Ø­ Ù…Ø­ØªØ¬Ø²Ø©';
        }
        if (str_starts_with($code, '233')) {
            return 'Ø§Ø­ØªÙŠØ§Ø·ÙŠØ§Øª Ø£Ø®Ø±Ù‰';
        }
        return 'Ø­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ©';
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
                <h4 class="page-title font-family-cairo fw-bold">Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ© Ø§Ù„Ø¹Ù…ÙˆÙ…ÙŠØ©</h4>
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
                                <label class="font-family-cairo fw-bold">Ø§Ø³Ù… Ø§Ù„Ø´Ø±ÙƒØ©:</label>
                                <input type="text" wire:model="companyName" class="form-control"
                                    placeholder="Ø£Ø¯Ø®Ù„ Ø§Ø³Ù… Ø§Ù„Ø´Ø±ÙƒØ©">
                            </div>
                        </div>
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-family-cairo fw-bold">ØªØ§Ø±ÙŠØ® Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ© Ø§Ù„Ø¹Ù…ÙˆÙ…ÙŠØ©:</label>
                                <input type="date" wire:model="balanceSheetDate" class="form-control">
                            </div>
                        </div> --}}
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button wire:click="refreshBalanceSheet" class="btn btn-primary font-family-cairo">
                                <i class="fas fa-sync-alt"></i> ØªØ­Ø¯ÙŠØ« Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ©
                            </button>
                            <button wire:click="exportBalanceSheet" class="btn btn-success font-family-cairo ms-2">
                                <i class="fas fa-download"></i> ØªØµØ¯ÙŠØ± Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ©
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
                    <h5 class="card-title font-family-cairo fw-bold">Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ© Ø§Ù„Ø¹Ù…ÙˆÙ…ÙŠØ© -
                        {{ $companyName ?: 'Ø§Ø³Ù… Ø§Ù„Ø´Ø±ÙƒØ©' }}</h5>
                    <p class="text-muted font-family-cairo">ØªØ§Ø±ÙŠØ® Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ©:
                        {{ \Carbon\Carbon::parse($balanceSheetDate)->format('Y-m-d') }}</p>
                    <p class="text-muted font-family-cairo">(Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø¨Ø§Ù„Øº Ø¨Ø§Ù„Ø¹Ù…Ù„Ø© Ø§Ù„Ù…Ø­Ù„ÙŠØ©)</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="font-family-cairo fw-bold text-center text-white" style="width: 60%">Ø§Ù„Ø¨Ù†Ø¯</th>
                                    <th class="font-family-cairo fw-bold text-center text-white" style="width: 40%">Ø§Ù„Ù…Ø¨Ù„Øº</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ø§Ù„Ø£ØµÙˆÙ„ (Assets) -->
                                <tr class="table-primary">
                                    <td colspan="2" class="font-family-cairo fw-bold fs-5">Ø§Ù„Ø£ØµÙˆÙ„ (Assets)</td>
                                </tr>
                                
                                <!-- Ø§Ù„Ø£ØµÙˆÙ„ Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">Ø§Ù„Ø£ØµÙˆÙ„ Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© (Current Assets)</td>
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
                                    <td class="font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø£ØµÙˆÙ„ Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($currentAssetsTotal, 2) }}</td>
                                </tr>
                                
                                <!-- Ø§Ù„Ø£ØµÙˆÙ„ ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">Ø§Ù„Ø£ØµÙˆÙ„ ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© (Non-Current Assets)</td>
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
                                    <td class="font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø£ØµÙˆÙ„ ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($nonCurrentAssetsTotal, 2) }}</td>
                                </tr>
                                
                                <tr class="table-success">
                                    <td class="font-family-cairo fw-bold fs-5">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø£ØµÙˆÙ„</td>
                                    <td class="text-end font-family-cairo fw-bold fs-5">
                                        {{ number_format($totalAssets, 2) }}</td>
                                </tr>
                                
                                <!-- Ø§Ù„Ø®ØµÙˆÙ… ÙˆØ­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ© -->
                                <tr class="table-primary">
                                    <td colspan="2" class="font-family-cairo fw-bold fs-5">Ø§Ù„Ø®ØµÙˆÙ… ÙˆØ­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ©
                                        (Liabilities & Equity)</td>
                                </tr>
                                
                                <!-- Ø§Ù„Ø®ØµÙˆÙ… Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">Ø§Ù„Ø®ØµÙˆÙ… Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© (Current Liabilities)</td>
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
                                    <td class="font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø®ØµÙˆÙ… Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($currentLiabilitiesTotal, 2) }}</td>
                                </tr>
                                
                                <!-- Ø§Ù„Ø®ØµÙˆÙ… ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">Ø§Ù„Ø®ØµÙˆÙ… ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø© (Non-Current Liabilities)
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
                                    <td class="font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø®ØµÙˆÙ… ØºÙŠØ± Ø§Ù„Ù…ØªØ¯Ø§ÙˆÙ„Ø©</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($nonCurrentLiabilitiesTotal, 2) }}</td>
                                </tr>
                                
                                <tr class="table-warning">
                                    <td class="font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø®ØµÙˆÙ…</td>
                                    <td class="text-end font-family-cairo fw-bold">
                                        {{ number_format($totalLiabilities, 2) }}</td>
                                </tr>
                                
                                <!-- Ø­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ© -->
                                <tr class="table-info">
                                    <td class="font-family-cairo fw-bold">Ø­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ© (Equity)</td>
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
                                    <td class="font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ©</td>
                                    <td class="text-end font-family-cairo fw-bold">{{ number_format($totalEquity, 2) }}
                                    </td>
                                </tr>
                                
                                <tr class="table-success">
                                    <td class="font-family-cairo fw-bold fs-5">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø®ØµÙˆÙ… ÙˆØ­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ©</td>
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
                                    Ù…Ø¹Ø§Ø¯Ù„Ø© Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ© Ø§Ù„Ø¹Ù…ÙˆÙ…ÙŠØ©:
                                    Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø£ØµÙˆÙ„ ({{ number_format($totalAssets, 2) }})
                                    {{ $totalAssets == $totalLiabilities + $totalEquity ? '=' : 'â‰ ' }}
                                    Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø®ØµÙˆÙ… + Ø­Ù‚ÙˆÙ‚ Ø§Ù„Ù…Ù„ÙƒÙŠØ©
                                    ({{ number_format($totalLiabilities + $totalEquity, 2) }})
                                </h6>
                                @if ($totalAssets != $totalLiabilities + $totalEquity)
                                    <p class="font-family-cairo text-danger">
                                        ØªØ­Ø°ÙŠØ±: Ø§Ù„Ù…ÙŠØ²Ø§Ù†ÙŠØ© ØºÙŠØ± Ù…ØªÙˆØ§Ø²Ù†Ø©. Ø§Ù„ÙØ±Ù‚:
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


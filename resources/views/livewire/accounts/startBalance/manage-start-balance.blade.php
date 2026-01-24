<?php

use Illuminate\Support\Facades\Log;
use Livewire\Volt\Component;
use Modules\Accounts\Models\AccHead;
use Modules\Accounts\Services\AccountService;

new class extends Component
{
    public $formAccounts = [];

    public $allAccounts = [];

    public $search = '';

    public $filterType = 'all'; // all, assets, liabilities, equity, changed

    public $accountsTypes = [
        'assets' => '1%',
        'liabilities' => '2%',
        'equity' => '3%',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Fix: Use accountsTypes properly in query
        $query = AccHead::where('is_basic', 0)
            ->where(function ($q) {
                $q->where('code', 'like', '1%')
                    ->orWhere('code', 'like', '2%')
                    ->orWhere('code', 'like', '3%');
            })
            ->orderBy('code');

        $listAccounts = $query->get();

        $this->allAccounts = [];
        foreach ($listAccounts as $account) {
            $this->allAccounts[$account->id] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->aname,
                'current_start_balance' => (float) $account->start_balance,
                'new_start_balance' => null,
                'type' => $this->getAccountType($account->code),
            ];
        }

        $this->applyFilters();
    }

    private function getAccountType(string $code): string
    {
        if (str_starts_with($code, '1')) {
            return 'assets';
        }
        if (str_starts_with($code, '2')) {
            return 'liabilities';
        }
        if (str_starts_with($code, '3')) {
            return 'equity';
        }

        return 'other';
    }

    public function updatedSearch()
    {
        $this->applyFilters();
    }

    public function updatedFilterType()
    {
        $this->applyFilters();
    }

    public function applyFilters()
    {
        $this->formAccounts = [];

        foreach ($this->allAccounts as $id => $account) {
            if (! isset($account['id']) || $account['id'] === null) {
                continue;
            }

            // Apply search filter
            if ($this->search && ! $this->matchesSearch($account)) {
                continue;
            }

            // Apply type filter
            if ($this->filterType !== 'all' && $this->filterType !== 'changed') {
                if ($account['type'] !== $this->filterType) {
                    continue;
                }
            }

            // Apply changed filter
            if ($this->filterType === 'changed') {
                if (! isset($account['new_start_balance']) || $account['new_start_balance'] === null) {
                    continue;
                }
            }

            $accountId = (int) $account['id'];
            if ($accountId > 0) {
                $this->formAccounts[$accountId] = $account;
            }
        }
    }

    private function matchesSearch(array $account): bool
    {
        $search = strtolower($this->search);

        return str_contains(strtolower($account['code']), $search) ||
               str_contains(strtolower($account['name']), $search);
    }

    public function resetChanges()
    {
        foreach ($this->allAccounts as $id => $account) {
            $this->allAccounts[$id]['new_start_balance'] = null;
        }
        $this->applyFilters();
        session()->flash('info', 'تم إعادة تعيين جميع التغييرات');
    }

    public function updateStartBalance()
    {
        // Build map of account_id => new_start_balance for changed rows only
        $changed = [];
        foreach ($this->allAccounts as $formAccount) {
            if (! isset($formAccount['id']) || $formAccount['id'] === null) {
                continue;
            }
            if (isset($formAccount['new_start_balance']) && $formAccount['new_start_balance'] !== null) {
                $accountId = (int) $formAccount['id'];
                if ($accountId > 0) {
                    $changed[$accountId] = (float) $formAccount['new_start_balance'];
                }
            }
        }

        if (count($changed) === 0) {
            session()->flash('error', 'يجب ادخال رصيد اول المدة الجديد للحسابات');

            return;
        }

        try {
            // Delegate to service layer for atomic updates and journal sync
            app(AccountService::class)->setStartBalances($changed);
            app(AccountService::class)->recalculateOpeningCapitalAndSyncJournal();

            $this->loadData();
            session()->flash('success', 'تم تحديث الرصيد بنجاح');
        } catch (\Throwable $e) {
            Log::error('Error updating start balance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'حدث خطأ ما أثناء تحديث الرصيد: '.$e->getMessage());
        }
    }

    public function getChangedCountProperty(): int
    {
        $count = 0;
        foreach ($this->allAccounts as $account) {
            if (isset($account['new_start_balance']) && $account['new_start_balance'] !== null) {
                $count++;
            }
        }

        return $count;
    }

    public function getTotalDebitProperty(): float
    {
        $total = 0.0;
        foreach ($this->allAccounts as $account) {
            $balance = isset($account['new_start_balance']) && $account['new_start_balance'] !== null
                ? (float) $account['new_start_balance']
                : (float) $account['current_start_balance'];
            if ($balance > 0) {
                $total += $balance;
            }
        }

        return $total;
    }

    public function getTotalCreditProperty(): float
    {
        $total = 0.0;
        foreach ($this->allAccounts as $account) {
            $balance = isset($account['new_start_balance']) && $account['new_start_balance'] !== null
                ? (float) $account['new_start_balance']
                : (float) $account['current_start_balance'];
            if ($balance < 0) {
                $total += abs($balance);
            }
        }

        return $total;
    }

    public function getDifferenceProperty(): float
    {
        return $this->totalDebit - $this->totalCredit;
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;" x-data="{ showConfirm: false }">
    <!-- Flash Messages -->
    <div class="row mb-3">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <i class="las la-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" @click="show = false"></button>
            </div>
        @elseif (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <i class="las la-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" @click="show = false"></button>
            </div>
        @elseif (session()->has('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <i class="las la-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" @click="show = false"></button>
            </div>
        @endif
    </div>

    <!-- Totals Summary -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <div class="text-center">
                                <small class="text-muted d-block mb-1">إجمالي المدين</small>
                                <span class="fw-bold text-primary font-14">
                                    {{ number_format($this->totalDebit, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <small class="text-muted d-block mb-1">إجمالي الدائن</small>
                                <span class="fw-bold text-danger font-14">
                                    {{ number_format($this->totalCredit, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <small class="text-muted d-block mb-1">الفرق</small>
                                <span class="fw-bold font-14 @if($this->difference > 0) text-success @elseif($this->difference < 0) text-danger @else text-muted @endif">
                                    {{ number_format($this->difference, 2) }}
                                </span>
                                @if($this->difference != 0)
                                    <small class="text-muted d-block mt-1">
                                        <i class="las la-info-circle me-1"></i>
                                        الفرق سيذهب للشريك الرئيسي
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">البحث</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                wire:model.live.debounce.300ms="search" 
                                placeholder="ابحث بالكود أو الاسم..."
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">فلترة حسب النوع</label>
                            <select class="form-control" wire:model.live="filterType">
                                <option value="all">الكل</option>
                                <option value="assets">أصول</option>
                                <option value="liabilities">خصوم</option>
                                <option value="equity">حقوق ملكية</option>
                                <option value="changed">المعدلة فقط</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <div class="d-flex gap-2 justify-content-end">
                                @if ($this->changedCount > 0)
                                    <button 
                                        type="button"
                                        class="btn btn-outline-danger" 
                                        wire:click="resetChanges"
                                    >
                                        <i class="las la-undo me-1"></i>
                                        إعادة تعيين ({{ $this->changedCount }})
                                    </button>
                                @endif
                                <button 
                                    type="button"
                                    class="btn btn-main" 
                                    wire:click="updateStartBalance"
                                    wire:target="updateStartBalance"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove wire:target="updateStartBalance">
                                        <i class="las la-sync me-1"></i>
                                        تحديث
                                    </span>
                                    <span wire:loading wire:target="updateStartBalance">
                                        <i class="las la-spinner la-spin me-1"></i>
                                        جاري التحديث...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @if ($this->changedCount > 0)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    <i class="las la-info-circle me-2"></i>
                                    عدد الحسابات المعدلة: <strong>{{ $this->changedCount }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <x-table-export-actions 
                        table-id="updateStartBalance-table" 
                        filename="updateStartBalance-table"
                        excel-label="تصدير Excel" 
                        pdf-label="تصدير PDF" 
                        print-label="طباعة" 
                    />

                    <div class="table-responsive">
                        <table id="updateStartBalance-table" class="table table-bordered table-sm table-striped">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th style="width: 8%" class="font-family-cairo fw-bold font-14">الكود</th>
                                    <th style="width: 25%" class="font-family-cairo fw-bold font-14">الاسم</th>
                                    <th style="width: 12%" class="font-family-cairo fw-bold font-14">النوع</th>
                                    <th style="width: 15%" class="font-family-cairo fw-bold font-14">رصيد اول المدة الحالي</th>
                                    <th style="width: 15%" class="font-family-cairo fw-bold font-14">رصيد اول المدة الجديد</th>
                                    <th style="width: 15%" class="font-family-cairo fw-bold font-14">الفرق</th>
                                    <th style="width: 10%" class="font-family-cairo fw-bold font-14">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="items_table_body">
                                @forelse ($formAccounts as $formAccount)
                                    @php
                                        $isChanged = isset($formAccount['new_start_balance']) && $formAccount['new_start_balance'] !== null;
                                        $newBalance = $isChanged ? (float) $formAccount['new_start_balance'] : $formAccount['current_start_balance'];
                                        $difference = $newBalance - $formAccount['current_start_balance'];
                                        // Accounts that cannot be edited: 3101 (capital), 1104% (warehouses), 2107 (customer points), 110301 (cash customer), 110501 (receivables portfolio), 210301 (payables portfolio), 210101 (cash supplier)
                                        $isEditable = !str_starts_with($formAccount['code'], '3101')
                                            && !str_starts_with($formAccount['code'], '1104')
                                            && $formAccount['code'] !== '2107'
                                            && $formAccount['code'] !== '110301'
                                            && $formAccount['code'] !== '110501'
                                            && $formAccount['code'] !== '210301'
                                            && $formAccount['code'] !== '210101';
                                    @endphp
                                    <tr 
                                        data-item-id="{{ $formAccount['id'] }}"
                                        class="@if($isChanged) table-warning @endif"
                                        style="@if($isChanged) background-color: #fff3cd !important; @endif"
                                    >
                                        <td>
                                            <p class="font-hold fw-bold font-16 text-center mb-0">{{ $formAccount['code'] }}</p>
                                        </td>
                                        <td>
                                            <p class="font-hold fw-bold font-16 text-center mb-0">{{ $formAccount['name'] }}</p>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($formAccount['type'] === 'assets') bg-primary
                                                @elseif($formAccount['type'] === 'liabilities') bg-danger
                                                @elseif($formAccount['type'] === 'equity') bg-success
                                                @else bg-secondary
                                                @endif
                                            ">
                                                @if($formAccount['type'] === 'assets') أصول
                                                @elseif($formAccount['type'] === 'liabilities') خصوم
                                                @elseif($formAccount['type'] === 'equity') حقوق ملكية
                                                @else أخرى
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <p class="font-hold fw-bold font-16 text-center mb-0 @if ($formAccount['current_start_balance'] < 0) text-danger @endif">
                                                {{ number_format($formAccount['current_start_balance'] ?? 0, 2) }}
                                            </p>
                                        </td>
                                        <td>
                                            @if ($isEditable)
                                                <input 
                                                    type="number" 
                                                    step="0.01"
                                                    class="form-control text-center @if ($newBalance < 0) text-danger @endif"
                                                    wire:model.blur="allAccounts.{{ $formAccount['id'] }}.new_start_balance"
                                                    wire:change="applyFilters"
                                                    placeholder="رصيد اول المدة الجديد"
                                                />
                                            @else
                                                <p class="text-muted text-center mb-0">
                                                    <small>غير قابل للتعديل</small>
                                                </p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($isChanged)
                                                <p class="font-hold fw-bold font-16 text-center mb-0 
                                                    @if ($difference > 0) text-success
                                                    @elseif ($difference < 0) text-danger
                                                    @else text-muted
                                                    @endif
                                                ">
                                                    @if ($difference > 0)
                                                        <i class="las la-arrow-up"></i> +{{ number_format($difference, 2) }}
                                                    @elseif ($difference < 0)
                                                        <i class="las la-arrow-down"></i> {{ number_format($difference, 2) }}
                                                    @else
                                                        {{ number_format($difference, 2) }}
                                                    @endif
                                                </p>
                                            @else
                                                <p class="text-muted text-center mb-0">-</p>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('account-movement', ['accountId' => $formAccount['id']]) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="عرض حركات الحساب"
                                               target="_blank">
                                                <i class="las la-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-muted mb-0">لا توجد حسابات متطابقة مع الفلتر المحدد</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (count($formAccounts) > 0)
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-0">
                                    إجمالي الحسابات المعروضة: <strong>{{ count($formAccounts) }}</strong>
                                </p>
                            </div>
                            <div>
                                <button 
                                    type="button"
                                    class="btn btn-main" 
                                    wire:click="updateStartBalance"
                                    wire:target="updateStartBalance"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove wire:target="updateStartBalance">
                                        <i class="las la-sync me-1"></i>
                                        تحديث التغييرات
                                    </span>
                                    <span wire:loading wire:target="updateStartBalance">
                                        <i class="las la-spinner la-spin me-1"></i>
                                        جاري التحديث...
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function startBalanceManager() {
    'use strict';

    function initKeyboardNavigation() {
        document.querySelectorAll('form, .card').forEach(function(container) {
            container.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'textarea') {
                    const input = e.target;
                    if (input.type === 'number' && input.closest('td')) {
                        e.preventDefault();
                        const inputs = Array.from(container.querySelectorAll(
                            'input[type="number"]:not([readonly]), input[type="text"]:not([readonly]), select'
                        ));
                        const idx = inputs.indexOf(input);
                        if (idx > -1 && idx < inputs.length - 1) {
                            inputs[idx + 1].focus();
                        }
                    }
                }
            });
        });
    }

    function safeApexChartsInit() {
        // Only initialize charts if the container exists and is visible
        if (typeof ApexCharts !== 'undefined') {
            const chartContainers = document.querySelectorAll('[id*="chart"], [class*="chart"]');
            chartContainers.forEach(function(container) {
                if (!container || !container.offsetParent) {
                    return;
                }
            });
        }
    }

    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initKeyboardNavigation();
                safeApexChartsInit();
            });
        } else {
            initKeyboardNavigation();
            safeApexChartsInit();
        }
    }

    init();
})();
</script>
@endpush

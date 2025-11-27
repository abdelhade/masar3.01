<?php

use Livewire\Volt\Component;
use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Services\AccountService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

new class extends Component {
    public $formAccounts = [];
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
        $listAccounts = AccHead::where(function ($query) {
            foreach ($this->accountsTypes as $accountType) {
                $query->orWhere('code', 'like', '1%')->orWhere('code', 'like', '2%')->orWhere('code', 'like', '3%');
            }
        })
            ->where('is_basic', 0)
            ->get();

        foreach ($listAccounts as $account) {
            $this->formAccounts[$account->id] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->aname,
                'current_start_balance' => (float) $account->start_balance,
                'new_start_balance' => null,
            ];
        }
    }

    public function updateStartBalance()
    {
        // Build map of account_id => new_start_balance for changed rows only
        $changed = [];
        foreach ($this->formAccounts as $formAccount) {
            if (isset($formAccount['new_start_balance']) && $formAccount['new_start_balance'] !== null) {
                $changed[$formAccount['id']] = (float) $formAccount['new_start_balance'];
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
            ]);
            session()->flash('error', 'حدث خطأ ما أثناء تحديث الرصيد: ' . $e->getMessage());
        }
    }

    // Legacy methods removed: parent/capital recalculation and journal sync now handled by AccountService
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="row">
        @if (session()->has('success'))
            <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        @elseif (session()->has('error'))
            <div class="alert alert-danger" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <form wire:submit="updateStartBalance" wire:target="updateStartBalance" wire:loading.attr="disabled">
                @csrf
                <style>
                    .custom-table-hover tbody tr:hover {
                        background-color: #f5e9d7 !important;
                        /* لون مختلف عند المرور */
                    }
                </style>

                <x-table-export-actions table-id="updateStartBalance-table" filename="updateStartBalance-table"
                    excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                <table id="updateStartBalance-table"
                    class="table table-bordered table-sm table-striped custom-table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 10%" class="font-family-cairo fw-bold font-14">الكود</th>
                            <th style="width: 20%" class="font-family-cairo fw-bold font-14">الاسم</th>
                            <th style="width: 15%" class="font-family-cairo fw-bold font-14">رصيد اول المدة الحالي</th>
                            <th style="width: 15%" class="font-family-cairo fw-bold font-14">رصيد اول المدة الجديد</th>
                        </tr>
                    </thead>
                    <tbody id="items_table_body">
                        @foreach ($formAccounts as $formAccount)
                            <tr data-item-id="{{ $formAccount['id'] }}">
                                <td>
                                    <p class="font-family-cairo fw-bold font-16 text-center">{{ $formAccount['code'] }}
                                    </p>
                                </td>
                                <td>
                                    <p class="font-family-cairo fw-bold font-16 text-center">{{ $formAccount['name'] }}
                                        - <a
                                            href="{{ route('account-movement', ['accountId' => $formAccount['id']]) }}">
                                            <i class="las la-eye fa-lg" title="عرض حركات الحساب"></i>
                                        </a></p>
                                </td>
                                <td>
                                    <p
                                        class="font-family-cairo fw-bold font-16 text-center @if ($formAccount['current_start_balance'] < 0) text-danger @endif">
                                        {{ number_format($formAccount['current_start_balance'] ?? 0, 2) }}</p>
                                </td>
                                <td>
                                    @if (!Str::startsWith($formAccount['code'], '3101') && !Str::startsWith($formAccount['code'], '1104'))
                                        <input type="number" step="0.01"
                                            wire:model.blur="formAccounts.{{ $formAccount['id'] }}.new_start_balance"
                                            class="form-control form-control-sm new-balance-input font-family-cairo fw-bold font-16 @if (($formAccounts[$formAccount['id']]['new_start_balance'] ?? 0) < 0) text-danger @endif"
                                            placeholder="رصيد اول المدة الجديد" style="padding:2px;height:30px;"
                                            x-on:keydown.enter.prevent>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <button type="submit" class="btn btn-main m-3" wire:click="$refresh"
                    wire:target="updateStartBalance" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updateStartBalance">
                        تحديث
                    </span>
                    <span wire:loading wire:target="updateStartBalance">
                        جاري التحديث...
                    </span>
                </button>
            </form>

        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'textarea') {
                        e.preventDefault();
                        const inputs = Array.from(form.querySelectorAll(
                            'input:not([type=hidden]):not([readonly]), select, textarea'
                        ));
                        const idx = inputs.indexOf(e.target);
                        if (idx > -1 && idx < inputs.length - 1) {
                            // انتقل للحقل التالي
                            inputs[idx + 1].focus();
                        } else if (idx === inputs.length - 1) {
                            // إذا كان في آخر حقل، انتقل إلى زر التحديث أو الحفظ
                            const submitBtn = form.querySelector(
                                'button[type="submit"], input[type="submit"]');
                            if (submitBtn) {
                                submitBtn.focus();
                                // عند الضغط على Enter مرة أخرى على الزر قم بالتحديث أو الحفظ
                                submitBtn.addEventListener('keydown', function handler(ev) {
                                    if (ev.key === 'Enter' || ev.keyCode === 13) {
                                        ev.preventDefault();
                                        submitBtn.click();
                                    }
                                    // إزالة الحدث بعد أول استخدام لمنع التكرار
                                    submitBtn.removeEventListener('keydown', handler);
                                });
                            }
                        }
                    }
                });
            });
        });
    </script>
@endpush

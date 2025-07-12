<?php

use Livewire\Volt\Component;
use App\Models\AccHead;
use Illuminate\Support\Facades\DB;
use App\Models\OperHead;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use Modules\Settings\Models\PublicSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

new class extends Component {
    public $accounts;
    public $accountsTypes = [
        'client' => '122%',
        'supplier' => '211%',
        'fund' => '121%',
        'bank' => '124%',
        'expense' => '44%',
        'revenue' => '32%',
        'creditor' => '212%',
        'debtor' => '125%',
        'partner' => '221%',
        'asset' => '11%',
        'employee' => '213%',
        'rentable' => '112%',
        'store' => '123%',
    ];
    public $capitalAccountsTypes = [
        'client' => '122%',
        'supplier' => '211%',
        'fund' => '121%',
        'bank' => '124%',
        // 'expense' => '44%',
        // 'revenue' => '32%',
        'creditor' => '212%',
        'debtor' => '125%',
        // 'partner' => '221%',
        'asset' => '11%',
        'employee' => '213%',
        'rentable' => '112%',
        'store' => '123%',
    ];

    public $current_accounts_opening_balance = [];
    public $new_accounts_opening_balance = [];
    // public $adjustment = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->accounts = AccHead::where(function ($query) {
            foreach ($this->accountsTypes as $typePattern) {
                $query->orWhere('code', 'like', $typePattern);
            }
        })->where('is_basic', 0)->get();

        foreach ($this->accounts as $account) {
            $this->current_accounts_opening_balance[$account->id] = (float) $account->start_balance;
            if (!Str::startsWith($account->code, '221') && !Str::startsWith($account->code, '123')) {
                $this->new_accounts_opening_balance[$account->id] = null;
            }
        }
    }

    public function updateStartBalance()
    {
        $newBalances = array_filter($this->new_accounts_opening_balance, fn($value) => !is_null($value));

        if (empty($newBalances)) {
            session()->flash('error', 'لم يتم إدخال بيانات للتحديث');
            return;
        }

        try {
            DB::beginTransaction();

            $accounts = AccHead::findMany(array_keys($newBalances));

            foreach ($accounts as $account) {
                $newBalance = $newBalances[$account->id];
                $oldBalance = $account->start_balance;

                $account->start_balance = $newBalance;
                $account->save();

                if ($account->parent_id) {
                    $this->updateParentBalance($account, $oldBalance, $newBalance);
                }
            }

            $this->updateCapitalBalance();
            $this->createOperationAndJournal($newBalances);

            $this->loadData();
            session()->flash('success', 'تم تحديث الرصيد بنجاح');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating start balance', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'حدث خطأ ما أثناء تحديث الرصيد: ' . $e->getMessage());
        }
    }

    public function updateParentBalance($account, $accountOldStartBalance, $newBalance)
    {
        if (!$account->parent_id) {
            return;
        }
        $parent = AccHead::find($account->parent_id);
        if ($parent) {
            $parent->start_balance = $parent->start_balance - $accountOldStartBalance + $newBalance;
            $parent->save();
            if ($parent->parent_id) {
                $this->updateParentBalance($parent, $accountOldStartBalance, $newBalance);
            }
        }
    }

    public function updateCapitalBalance()
    {
        $accounts = AccHead::where(function ($query) {
            foreach ($this->capitalAccountsTypes as $capitalAccountType) {
                $query->orWhere('code', 'like', $capitalAccountType);
            }
        })->where('is_basic', 0)->get();

        $balance = $accounts->sum('start_balance') * -1;
        $basicCapitalAccount = AccHead::where('code', '=', '2211')->first();

        if ($basicCapitalAccount) {
            $oldBalance = $basicCapitalAccount->start_balance;
            $basicCapitalAccount->start_balance = $balance;
            $basicCapitalAccount->save();
            if ($basicCapitalAccount->parent_id) {
                $this->updateParentBalance($basicCapitalAccount, $oldBalance, $balance);
            }
        }
    }

    public function createOperationAndJournal(array $newBalances)
    {
        $startDate = PublicSetting::where('key', 'start_date')->value('value');
        $userId = Auth::id();

        $oper = OperHead::updateOrCreate(
            ['pro_type' => 61],
            [
                'is_journal' => 1,
                'journal_type' => 1,
                'info' => 'تسجيل الارصده الافتتاحيه للحسابات',
                'pro_date' => $startDate,
                'user' => $userId,
            ],
        );

        $journalId = JournalHead::where('pro_type', 61)->where('op_id', $oper->id)->value('journal_id') ?? JournalHead::max('journal_id') + 1;

        $accounts = AccHead::where(function ($query) {
            foreach ($this->capitalAccountsTypes as $capitalAccountType) {
                $query->orWhere('code', 'like', $capitalAccountType);
            }
        })->where('is_basic', 0)->get();

        $totalDebit = $accounts->where('start_balance', '>', 0)->sum('start_balance');
        $totalCredit = $accounts->where('start_balance', '<', 0)->sum('start_balance');

        $capitalAccount = AccHead::where('code', '=', '2211')->first();
        if ($capitalAccount) {
            $totalCredit += $capitalAccount->start_balance;
        }

        if (round($totalDebit, 2) == round(-$totalCredit, 2)) {
            JournalHead::updateOrCreate(
                ['journal_id' => $journalId, 'pro_type' => 61],
                [
                    'op_id' => $oper->id,
                    'total' => $totalDebit,
                    'date' => $startDate,
                    'op2' => $oper->id,
                    'user' => $userId,
                ],
            );

            foreach ($newBalances as $accountId => $newBalance) {
                if ($newBalance > 0) {
                    JournalDetail::updateOrCreate(
                        ['journal_id' => $journalId, 'account_id' => $accountId, 'op_id' => $oper->id],
                        ['debit' => $newBalance, 'credit' => 0, 'type' => 1]
                    );
                } elseif ($newBalance < 0) {
                    JournalDetail::updateOrCreate(
                        ['journal_id' => $journalId, 'account_id' => $accountId, 'op_id' => $oper->id],
                        ['debit' => 0, 'credit' => -$newBalance, 'type' => 1]
                    );
                } else {
                    JournalDetail::where('journal_id', $journalId)->where('account_id', $accountId)->where('op_id', $oper->id)->delete();
                }
            }

            if ($capitalAccount) {
                JournalDetail::updateOrCreate(
                    ['journal_id' => $journalId, 'account_id' => $capitalAccount->id, 'op_id' => $oper->id],
                    [
                        'debit' => 0,
                        'credit' => -$capitalAccount->start_balance,
                        'type' => 1,
                    ],
                );
            }
            // delete the journal if the capital account balance is 0
            if ($capitalAccount->start_balance == 0) {
                // delete the journal details
                JournalDetail::where('journal_id', $journalId)->where('op_id', $oper->id)->where('account_id', $capitalAccount->id)->delete();
                JournalHead::where('journal_id', $journalId)->delete();
            }
        } else {
            throw new \Exception('الحسابات المدينه لا تتساوي مع الحسابات الدائنه');
        }
    }
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
                <table class="table table-bordered table-sm table-striped table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 10%" class="font-family-cairo fw-bold font-14">الكود</th>
                            <th style="width: 20%" class="font-family-cairo fw-bold font-14">الاسم</th>
                            <th style="width: 15%" class="font-family-cairo fw-bold font-14">رصيد اول المده الحالي</th>
                            <th style="width: 15%" class="font-family-cairo fw-bold font-14">رصيد اول المده الجديد</th>
                            {{-- <th style="width: 15%" class="font-family-cairo fw-bold font-14">كميه التسويه</th> --}}
                        </tr>
                    </thead>
                    <tbody id="items_table_body">
                        @foreach ($accounts as $account)
                            <tr data-item-id="{{ $account->id }}">
                                <td>
                                    <p class="font-family-cairo fw-bold font-16 text-center">{{ $account->code }}</p>
                                </td>
                                <td>
                                    <p class="font-family-cairo fw-bold font-16 text-center">{{ $account->aname }}</p>
                                </td>
                                <td>
                                    <p
                                        class="font-family-cairo fw-bold font-16 text-center @if ($current_accounts_opening_balance[$account->id] < 0) text-danger @endif">
                                        {{ number_format($current_accounts_opening_balance[$account->id] ?? 0, 2) }}</p>
                                </td>
                                <td>
                                    @if (!Str::startsWith($account->code, '221') && !Str::startsWith($account->code, '123'))
                                        <input type="number" step="0.01"
                                            wire:model.blur="new_accounts_opening_balance.{{ $account->id }}"
                                            class="form-control form-control-sm new-balance-input font-family-cairo fw-bold font-16 @if (($new_accounts_opening_balance[$account->id] ?? 0) < 0) text-danger @endif"
                                            placeholder="رصيد اول المده الجديد" style="padding:2px;height:30px;"
                                            x-on:keydown.enter.prevent>
                                        {{-- @else
                                        <p class="font-family-cairo fw-bold font-16 text-center">
                                            {{ $account->start_balance ?? 0 }}
                                        </p> --}}
                                    @endif
                                </td>
                                {{-- <td>
                                    <p class="font-family-cairo fw-bold font-16 text-center @if (($new_accounts_opening_balance[$account->id] ?? 0) - ($current_accounts_opening_balance[$account->id] ?? 0) < 0) text-danger @endif">
                                        {{ number_format(($new_accounts_opening_balance[$account->id] ?? 0) - ($current_accounts_opening_balance[$account->id] ?? 0), 2) }}
                                    </p>
                                </td> --}}
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary m-3" wire:click="$refresh"
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
                                // عند الضغط على Enter مرة أخرى على الزر، قم بالتحديث أو الحفظ
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

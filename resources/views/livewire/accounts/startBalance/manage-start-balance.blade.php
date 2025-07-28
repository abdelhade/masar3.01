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
    public $formAccounts = [];
    public $accountsTypes = [
        'client' => '122%',
        'supplier' => '211%',
        'fund' => '121%',
        'bank' => '124%',
        // 'expense' => '44%',
        // 'revenue' => '32%',
        'creditor' => '212%',
        'debtor' => '125%',
        'partner' => '231%',
        'asset' => '11%',
        'employee' => '213%',
        'rentable' => '112%',
        'store' => '123%',
    ];



    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $listAccounts = AccHead::where(function ($query) {
            foreach ($this->accountsTypes as $accountType) {
                $query->orWhere('code', 'like', $accountType);
            }
        })->where('is_basic', 0)->get();

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
        // if the formAccounts all new_start_balance is null, return
        if (count(array_filter($this->formAccounts, function ($formAccount) {
            return $formAccount['new_start_balance'] !== null;
        })) === 0) {
            session()->flash('error', 'يجب ادخال رصيد اول المده الجديد للحسابات');
            return;
        }
        try {
            DB::beginTransaction();
            foreach ($this->formAccounts as $formAccount) {
                $account = AccHead::findOrFail($formAccount['id']);
                if ($formAccount['new_start_balance'] != null) {
                    $account->start_balance = $formAccount['new_start_balance'];
                    $account->balance = $account->balance - $formAccount['current_start_balance'] + $formAccount['new_start_balance'];
                    $account->save();
                    if ($account->parent_id) {
                        $this->updateParentBalance($account, $formAccount['current_start_balance'], $formAccount['new_start_balance']);
                    }
                }
            }

            $this->updateParentCapitalBalance();

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

    public function updateParentBalance($account, $accountOldStartBalance, $accountNewStartBalance)
    {
        if (!$account->parent_id) {
            return;
        }
        $parent = AccHead::findOrFail($account->parent_id);
        if ($parent) {
            $parent->start_balance = $parent->start_balance - $accountOldStartBalance + $accountNewStartBalance;
            $parent->save();
            if ($parent->parent_id) {
                $this->updateParentBalance($parent, $accountOldStartBalance, $accountNewStartBalance);
            }
        }
    }

    public function updateParentCapitalBalance()
    {
        $parentCapital = AccHead::where('code', '=', '2311')->first();
        $stotresAndCapitalsAccountsIds = AccHead::where('code', 'like', '123%')->orWhere('code', 'like', '231%')->pluck('id')->toArray();
        $oldAllTotalParentCapital = $parentCapital->start_balance;
        $totalFormAccountsDebit = 0;
        $totalFormAccountsCredit = 0;
        foreach ($this->formAccounts as  $formAccount) {
            if (in_array($formAccount['id'], $stotresAndCapitalsAccountsIds)) {
                continue;
            }
            if ($formAccount['new_start_balance'] != null && $formAccount['new_start_balance'] > 0) {
                $totalFormAccountsDebit += $formAccount['new_start_balance'];
            }
            if ($formAccount['new_start_balance'] != null && $formAccount['new_start_balance'] < 0) {
                $totalFormAccountsCredit += $formAccount['new_start_balance'];
            }
            if ($formAccount['new_start_balance'] === null  ) {
                if ($formAccount['current_start_balance'] > 0) {
                    $totalFormAccountsDebit += $formAccount['current_start_balance'];
                } elseif ($formAccount['current_start_balance'] < 0) {
                    $totalFormAccountsCredit += $formAccount['current_start_balance'];
                }
            }
        }
        $newTotalParentCapitalFromStartAccountsBalanceForm = ($totalFormAccountsDebit + $totalFormAccountsCredit) * -1;
        $itemSartBalanceJournalHeads = JournalHead::where('pro_type', 60)->pluck('id');
        $totalParentCapitalFromItemsStartBalance = 0;
        foreach ($itemSartBalanceJournalHeads as $itemSartBalanceJournalHead) {
            $totalParentCapitalFromItemsStartBalance += JournalDetail::where('journal_id', $itemSartBalanceJournalHead)->where('account_id', $parentCapital->id)->value('credit');
        }
        $newAllTotalParentCapital = $newTotalParentCapitalFromStartAccountsBalanceForm + ($totalParentCapitalFromItemsStartBalance * -1);
        // dd($totalFormAccountsCredit, $totalFormAccountsDebit, $newTotalParentCapitalFromStartAccountsBalanceForm, $totalParentCapitalFromItemsStartBalance, $newAllTotalParentCapital);
        $parentCapital->start_balance = $newAllTotalParentCapital;
        $parentCapital->save();
        if ($parentCapital->parent_id) {
            $this->updateParentBalance($parentCapital, $oldAllTotalParentCapital, $newAllTotalParentCapital);
        }
        $this->createOperationAndJournal($newTotalParentCapitalFromStartAccountsBalanceForm, $totalFormAccountsDebit, $totalFormAccountsCredit);
    }

    public function createOperationAndJournal($newTotalParentCapitalFromStartAccountsBalanceForm, $totalFormAccountsDebit, $totalFormAccountsCredit)
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

        $totalDebit = $totalFormAccountsDebit;
        $totalCredit = $totalFormAccountsCredit;

        // if (round($totalDebit, 2) == round(-$totalCredit, 2)) {
            $capitalAccount = AccHead::where('code', '=', '2311')->first();
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

            foreach ($this->formAccounts as $formAccount) {
                if ($formAccount['new_start_balance'] !== null && $formAccount['new_start_balance'] > 0) {
                    JournalDetail::updateOrCreate(
                        ['journal_id' => $journalId, 'account_id' => $formAccount['id'], 'op_id' => $oper->id],
                        ['debit' => $formAccount['new_start_balance'], 'credit' => 0, 'type' => 1]
                    );
                } elseif ($formAccount['new_start_balance'] !== null && $formAccount['new_start_balance'] < 0) {
                    JournalDetail::updateOrCreate(
                        ['journal_id' => $journalId, 'account_id' => $formAccount['id'], 'op_id' => $oper->id],
                        ['debit' => 0, 'credit' => -$formAccount['new_start_balance'], 'type' => 1]
                    );
                } elseif ($formAccount['new_start_balance'] !== null && $formAccount['new_start_balance'] == 0) {
                    JournalDetail::where('journal_id', $journalId)->where('account_id', $formAccount['id'])->where('op_id', $oper->id)->delete();
                } elseif ($formAccount['new_start_balance'] === null) {
                    continue;
                }
            }

            if ($newTotalParentCapitalFromStartAccountsBalanceForm !== null && $newTotalParentCapitalFromStartAccountsBalanceForm > 0) {
                    JournalDetail::updateOrCreate(
                    ['journal_id' => $journalId, 'account_id' => $capitalAccount->id, 'op_id' => $oper->id],
                    [
                        'debit' => 0,
                        'credit' => $newTotalParentCapitalFromStartAccountsBalanceForm,
                        'type' => 1,
                    ],
                );
            } elseif ($newTotalParentCapitalFromStartAccountsBalanceForm !== null && $newTotalParentCapitalFromStartAccountsBalanceForm < 0) {
                JournalDetail::updateOrCreate(
                    ['journal_id' => $journalId, 'account_id' => $capitalAccount->id, 'op_id' => $oper->id],
                    [
                        'debit' => 0,
                        'credit' => -$newTotalParentCapitalFromStartAccountsBalanceForm,
                        'type' => 1,
                    ],
                );
            } elseif ($newTotalParentCapitalFromStartAccountsBalanceForm === null) {
                JournalDetail::where('journal_id', $journalId)->where('op_id', $oper->id)->where('account_id', $capitalAccount->id)->delete();
                JournalHead::where('journal_id', $journalId)->delete();
            }
            if ($capitalAccount->start_balance == 0) {
                JournalDetail::where('journal_id', $journalId)->where('op_id', $oper->id)->where('account_id', $capitalAccount->id)->delete();
                JournalHead::where('journal_id', $journalId)->delete();
            }
        // } else {
        //     throw new \Exception('الحسابات المدينه لا تتساوي مع الحسابات الدائنه');
        // }
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
                        @foreach ($formAccounts as $formAccount)
                            <tr data-item-id="{{ $formAccount['id'] }}">
                                <td>
                                    <p class="font-family-cairo fw-bold font-16 text-center">{{ $formAccount['code'] }}</p>
                                </td>
                                <td>
                                    <p class="font-family-cairo fw-bold font-16 text-center">{{ $formAccount['name'] }}</p>
                                </td>
                                <td>
                                    <p
                                        class="font-family-cairo fw-bold font-16 text-center @if ($formAccount['current_start_balance'] < 0) text-danger @endif">
                                        {{ number_format($formAccount['current_start_balance'] ?? 0, 2) }}</p>
                                </td>
                                <td>
                                    @if (!Str::startsWith($formAccount['code'], '231') && !Str::startsWith($formAccount['code'], '123'))
                                        <input type="number" step="0.01"
                                            wire:model.blur="formAccounts.{{ $formAccount['id'] }}.new_start_balance"
                                            class="form-control form-control-sm new-balance-input font-family-cairo fw-bold font-16 @if (($formAccounts[$formAccount['id']]['new_start_balance'] ?? 0) < 0) text-danger @endif"
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

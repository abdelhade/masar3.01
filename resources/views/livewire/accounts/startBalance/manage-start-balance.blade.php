<?php

use Livewire\Volt\Component;
use Modules\\Accounts\\Models\\AccHead;
use Illuminate\Support\Facades\DB;
use Modules\\Accounts\\Services\\AccountService;
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
            session()->flash('error', 'Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â§Ã˜Â¯Ã˜Â®Ã˜Â§Ã™â€ž Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â§Ã™Ë†Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¯Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â¬Ã˜Â¯Ã™Å Ã˜Â¯ Ã™â€žÃ™â€žÃ˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨Ã˜Â§Ã˜Âª');
            return;
        }

        try {
            // Delegate to service layer for atomic updates and journal sync
            app(AccountService::class)->setStartBalances($changed);
            app(AccountService::class)->recalculateOpeningCapitalAndSyncJournal();

            $this->loadData();
            session()->flash('success', 'Ã˜ÂªÃ™â€¦ Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â« Ã˜Â§Ã™â€žÃ˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­');
        } catch (\Throwable $e) {
            Log::error('Error updating start balance', [
                'message' => $e->getMessage(),
            ]);
            session()->flash('error', 'Ã˜Â­Ã˜Â¯Ã˜Â« Ã˜Â®Ã˜Â·Ã˜Â£ Ã™â€¦Ã˜Â§ Ã˜Â£Ã˜Â«Ã™â€ Ã˜Â§Ã˜Â¡ Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â« Ã˜Â§Ã™â€žÃ˜Â±Ã˜ÂµÃ™Å Ã˜Â¯: ' . $e->getMessage());
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
                        /* Ã™â€žÃ™Ë†Ã™â€  Ã™â€¦Ã˜Â®Ã˜ÂªÃ™â€žÃ™Â Ã˜Â¹Ã™â€ Ã˜Â¯ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â±Ã™Ë†Ã˜Â± */
                    }
                </style>

                <x-table-export-actions table-id="updateStartBalance-table" filename="updateStartBalance-table"
                    excel-label="Ã˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â± Excel" pdf-label="Ã˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â± PDF" print-label="Ã˜Â·Ã˜Â¨Ã˜Â§Ã˜Â¹Ã˜Â©" />

                <table id="updateStartBalance-table"
                    class="table table-bordered table-sm table-striped custom-table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 10%" class="font-family-cairo fw-bold font-14">Ã˜Â§Ã™â€žÃ™Æ’Ã™Ë†Ã˜Â¯</th>
                            <th style="width: 20%" class="font-family-cairo fw-bold font-14">Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã™â€¦</th>
                            <th style="width: 15%" class="font-family-cairo fw-bold font-14">Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â§Ã™Ë†Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¯Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ™Å </th>
                            <th style="width: 15%" class="font-family-cairo fw-bold font-14">Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â§Ã™Ë†Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¯Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â¬Ã˜Â¯Ã™Å Ã˜Â¯</th>
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
                                            <i class="las la-eye fa-lg" title="Ã˜Â¹Ã˜Â±Ã˜Â¶ Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨"></i>
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
                                            placeholder="Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â§Ã™Ë†Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¯Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â¬Ã˜Â¯Ã™Å Ã˜Â¯" style="padding:2px;height:30px;"
                                            x-on:keydown.enter.prevent>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary m-3" wire:click="$refresh"
                    wire:target="updateStartBalance" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updateStartBalance">
                        Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«
                    </span>
                    <span wire:loading wire:target="updateStartBalance">
                        Ã˜Â¬Ã˜Â§Ã˜Â±Ã™Å  Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«...
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
                            // Ã˜Â§Ã™â€ Ã˜ÂªÃ™â€šÃ™â€ž Ã™â€žÃ™â€žÃ˜Â­Ã™â€šÃ™â€ž Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â§Ã™â€žÃ™Å 
                            inputs[idx + 1].focus();
                        } else if (idx === inputs.length - 1) {
                            // Ã˜Â¥Ã˜Â°Ã˜Â§ Ã™Æ’Ã˜Â§Ã™â€  Ã™ÂÃ™Å  Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜Â­Ã™â€šÃ™â€žÃ˜Å’ Ã˜Â§Ã™â€ Ã˜ÂªÃ™â€šÃ™â€ž Ã˜Â¥Ã™â€žÃ™â€° Ã˜Â²Ã˜Â± Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â« Ã˜Â£Ã™Ë† Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â¸
                            const submitBtn = form.querySelector(
                                'button[type="submit"], input[type="submit"]');
                            if (submitBtn) {
                                submitBtn.focus();
                                // Ã˜Â¹Ã™â€ Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¶Ã˜ÂºÃ˜Â· Ã˜Â¹Ã™â€žÃ™â€° Enter Ã™â€¦Ã˜Â±Ã˜Â© Ã˜Â£Ã˜Â®Ã˜Â±Ã™â€° Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â²Ã˜Â±Ã˜Å’ Ã™â€šÃ™â€¦ Ã˜Â¨Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â« Ã˜Â£Ã™Ë† Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â¸
                                submitBtn.addEventListener('keydown', function handler(ev) {
                                    if (ev.key === 'Enter' || ev.keyCode === 13) {
                                        ev.preventDefault();
                                        submitBtn.click();
                                    }
                                    // Ã˜Â¥Ã˜Â²Ã˜Â§Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â¯Ã˜Â« Ã˜Â¨Ã˜Â¹Ã˜Â¯ Ã˜Â£Ã™Ë†Ã™â€ž Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã™â€žÃ™â€¦Ã™â€ Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜ÂªÃ™Æ’Ã˜Â±Ã˜Â§Ã˜Â±
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


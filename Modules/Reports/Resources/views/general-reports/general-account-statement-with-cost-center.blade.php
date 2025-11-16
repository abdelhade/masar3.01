@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>كشف حساب عام مع مركز تكلفة</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="account_id">الحساب:</label>
                    <select id="account_id" class="form-control" wire:model="accountId">
                        <option value="">اختر الحساب</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cost_center_id">مركز التكلفة:</label>
                    <select id="cost_center_id" class="form-control" wire:model="costCenterId">
                        <option value="">الكل</option>
                        @foreach($costCenters as $center)
                            <option value="{{ $center->id }}">{{ $center->code }} - {{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="from_date">من تاريخ:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">إلى تاريخ:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <button class="btn btn-primary" wire:click="generateReport">توليد التقرير</button>
                </div>
            </div>

            @if($selectedAccount)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <strong>الحساب المحدد:</strong> {{ $selectedAccount->code }} - {{ $selectedAccount->name }}
                        <br>
                        <strong>الرصيد الافتتاحي:</strong> {{ number_format($openingBalance, 2) }}
                    </div>
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم العملية</th>
                            <th>مركز التكلفة</th>
                            <th>البيان</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                            <th class="text-end">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->crtime ? \Carbon\Carbon::parse($transaction->crtime)->format('Y-m-d') : '---' }}</td>
                            <td>{{ $transaction->journalHead->journal_id ?? '---' }}</td>
                            <td>{{ $transaction->costCenter->name ?? '---' }}</td>
                            <td>{{ $transaction->info ?? '---' }}</td>
                            <td class="text-end">{{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '---' }}</td>
                            <td class="text-end">{{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '---' }}</td>
                            <td class="text-end">{{ number_format($transaction->running_balance, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($accountTransactions->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $accountTransactions->links() }}
                </div>
            @endif

            @if($selectedAccount)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-success">
                        <strong>الرصيد الختامي:</strong> {{ number_format($closingBalance, 2) }}
                    </div>
                </div>
            </div>
            @endif

            <!-- ملخص حسب مركز التكلفة -->
            @if($costCenterSummary->count() > 0)
            <div class="row mt-3">
                <div class="col-12">
                    <h4>ملخص حسب مركز التكلفة</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>مركز التكلفة</th>
                                    <th class="text-end">مدين</th>
                                    <th class="text-end">دائن</th>
                                    <th class="text-end">صافي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($costCenterSummary as $summary)
                                <tr>
                                    <td>{{ $summary->cost_center_name ?? 'بدون مركز تكلفة' }}</td>
                                    <td class="text-end">{{ number_format($summary->total_debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->total_credit, 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->net_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 
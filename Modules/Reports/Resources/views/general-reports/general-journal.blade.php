@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>اليومية العامة</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="from_date">من تاريخ:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">إلى تاريخ:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
                <div class="col-md-3">
                    <label for="account_id">الحساب:</label>
                    <select id="account_id" class="form-control" wire:model="accountId">
                        <option value="">الكل</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="journal_type">نوع القيد:</label>
                    <select id="journal_type" class="form-control" wire:model="journalType">
                        <option value="">الكل</option>
                        <option value="7">قيد يومية</option>
                        <option value="8">قيد يومية حسابات</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم القيد</th>
                            <th>الحساب</th>
                            <th>مدين</th>
                            <th>دائن</th>
                            <th>البيان</th>
                            <th>مركز التكلفة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journalDetails as $detail)
                        <tr>
                            <td>{{ $detail->head->date ? \Carbon\Carbon::parse($detail->head->date)->format('Y-m-d') : '---' }}</td>
                            <td>{{ $detail->head->journal_id ?? '---' }}</td>
                            <td>{{ $detail->accountHead->code ?? '---' }} - {{ $detail->accountHead->aname ?? '---' }}</td>
                            <td class="text-end">{{ $detail->debit > 0 ? number_format($detail->debit, 2) : '---' }}</td>
                            <td class="text-end">{{ $detail->credit > 0 ? number_format($detail->credit, 2) : '---' }}</td>
                            <td>{{ $detail->info ?? '---' }}</td>
                            <td>{{ $detail->costCenter->name ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($journalDetails->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $journalDetails->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 
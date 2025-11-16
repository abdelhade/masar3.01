@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>ميزان الحسابات</h2>
            <div class="text-muted">حتى تاريخ: {{ $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('Y-m-d') : now()->format('Y-m-d') }}</div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="as_of_date">حتى تاريخ:</label>
                    <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                </div>
                <div class="col-md-3">
                    <label for="account_group">مجموعة الحساب:</label>
                    <select id="account_group" class="form-control" wire:model="accountGroup">
                        <option value="">الكل</option>
                        <option value="1">الأصول</option>
                        <option value="2">الخصوم</option>
                        <option value="3">حقوق الملكية</option>
                        <option value="4">الإيرادات</option>
                        <option value="5">المصروفات</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">توليد التقرير</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>رقم الحساب</th>
                            <th>اسم الحساب</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                            <th class="text-end">الرصيد</th>
                            <th>نوع الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountBalances as $balance)
                        <tr>
                            <td>{{ $balance->code }}</td>
                            <td>{{ $balance->aname }}</td>
                            <td class="text-end">{{ $balance->debit > 0 ? number_format($balance->debit, 2) : '---' }}</td>
                            <td class="text-end">{{ $balance->credit > 0 ? number_format($balance->credit, 2) : '---' }}</td>
                            <td class="text-end">{{ number_format($balance->balance, 2) }}</td>
                            <td>
                                @if($balance->balance > 0)
                                    <span class="badge bg-primary">مدين</span>
                                @elseif($balance->balance < 0)
                                    <span class="badge bg-success">دائن</span>
                                @else
                                    <span class="badge bg-secondary">صفر</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="2">الإجمالي</th>
                            <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                            <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                            <th class="text-end">{{ number_format($totalBalance, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($accountBalances->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $accountBalances->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert {{ $totalDebit == $totalCredit ? 'alert-success' : 'alert-warning' }}">
                        <strong>النتيجة:</strong> 
                        @if($totalDebit == $totalCredit)
                            الميزان متوازن ✓
                        @else
                            الميزان غير متوازن - الفرق: {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
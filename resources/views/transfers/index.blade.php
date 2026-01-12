@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.transfers')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => 'التحويلات النقدية',
        'items' => [['label' => 'الرئيسية', 'url' => route('admin.dashboard')], ['label' => 'التحويلات النقدية']],
    ])


    <div class="card">
            <div class="card-header">
                @canany(['create cash-to-cash', 'create cash-to-bank', 'create bank-to-cash', 'create bank-to-bank','create transfers'])
                <div class="btn-group">
                    <button type="button" class="btn btn-main dropdown-toggle" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false">
                        {{ __('Add New') }} <i class="fas fa-plus me-2"></i>
                    </button>
                    <ul class="dropdown-menu">
                        @canany(['create cash-to-cash' , 'create transfers'])
                        <li><a class="dropdown-item" href="{{ route('transfers.create', ['type' => 'cash-to-cash']) }}">تحويل من صندوق إلى صندوق</a></li>
                        @endcanany
                        @canany(['create cash-to-bank' , 'create transfers'])
                        <li><a class="dropdown-item" href="{{ route('transfers.create', ['type' => 'cash-to-bank']) }}">تحويل من صندوق إلى بنك</a></li>
                        @endcanany
                        @canany(['create bank-to-cash' , 'create transfers'])
                        <li><a class="dropdown-item" href="{{ route('transfers.create', ['type' => 'bank-to-cash']) }}">تحويل من بنك إلى صندوق</a></li>
                        @endcanany
                        @canany(['create bank-to-bank' , 'create transfers'])
                        <li><a class="dropdown-item" href="{{ route('transfers.create', ['type' => 'bank-to-bank']) }}">تحويل من بنك إلى بنك</a></li>
                        @endcanany
                    </ul>
                </div>
            @endcanany

        </div>
        <div class="card-body">
            <div class="table-responsive">

                <x-table-export-actions table-id="transfers-table" filename="transfers-table" excel-label="تصدير Excel"
                    pdf-label="تصدير PDF" print-label="طباعة" />

                @php
                    $typeSlugs = [3 => 'cash-to-cash', 4 => 'cash-to-bank', 5 => 'bank-to-cash', 6 => 'bank-to-bank'];
                @endphp

                <table id="transfers-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>رقم العمليه</th>
                            <th>نوع العمليه</th>
                            <th>البيان</th>
                            @if(isMultiCurrencyEnabled())
                                <th>المبلغ (عملة أجنبية)</th>
                                <th>المبلغ (عملة محلية)</th>
                            @else
                                <th>المبلغ</th>
                            @endif
                            <th>مدين</th>
                            <th>دائن</th>
                            <th>الموظف</th>
                            <th>الموظف 2 </th>
                            <th>المستخدم</th>
                            <th>تم الانشاء في </th>
                            <th>ملاحظات</th>
                            <th>تم المراجعه</th>
                            {{-- @canany(['تعديل التحويلات النقدية', 'حذف التحويلات النقدية']) --}}
                                <th class="text-end">العمليات</th>
                            {{-- @endcanany --}}

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transfers as $transfer)
                            <tr>
                                <td> {{ $loop->iteration }}</td>
                                <td class="nowrap">{{ $transfer->pro_date }}</td>
                                <td>{{ $transfer->pro_id }}</td>
                                <td>{{ $transfer->type->ptext ?? '—' }}</td>
                                <td>{{ $transfer->details ?? '' }}</td>
                                @if(isMultiCurrencyEnabled())
                                    {{-- عمود العملة الأجنبية --}}
                                    <td>
                                        @if($transfer->currency_id && $transfer->currency_rate > 1)
                                            <span class="fw-bold">{{ number_format($transfer->pro_value / $transfer->currency_rate, 2) }}</span>
                                            <span class="text-muted">{{ $transfer->currency?->name ?? '' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    {{-- عمود العملة المحلية --}}
                                    <td>
                                        <span class="fw-bold">{{ number_format($transfer->pro_value, 2) }}</span>
                                    </td>
                                @else
                                    {{-- عمود واحد فقط إذا كانت العملات غير مفعلة --}}
                                    <td>
                                        <span class="fw-bold">{{ number_format($transfer->pro_value, 2) }}</span>
                                    </td>
                                @endif
                                <td>{{ $transfer->account1->aname ?? '' }}</td>
                                <td>{{ $transfer->account2->aname ?? '' }}</td>
                                <td>{{ $transfer->emp1->aname ?? '' }}</td>
                                <td>{{ $transfer->emp2->aname ?? '' }}</td>
                                <td>{{ $transfer->user_name->name }}</td>
                                <td>{{ $transfer->created_at }}</td>
                                <td>{{ $transfer->info }}</td>
                                <td>{{ $transfer->confirmed ? 'نعم' : 'لا' }}</td>
                                {{-- أظهر الأزرار بناءً على صلاحيات النوع أو صلاحية عامة --}}
                                    <td x-show="columns[16]">
                                        @php
                                            $slug = $typeSlugs[$transfer->pro_type] ?? null;
                                        @endphp

                                        @if(($slug && (\Illuminate\Support\Facades\Gate::allows("view {$slug}"))) || \Illuminate\Support\Facades\Gate::allows('view transfers'))
                                            <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-info btn-icon-square-sm" title="{{ __('Show') }}">
                                                <i class="las la-eye"></i>
                                            </a>
                                        @endif

                                        @if(($slug && (\Illuminate\Support\Facades\Gate::allows("edit {$slug}"))) || \Illuminate\Support\Facades\Gate::allows('edit transfers'))
                                            <button class="btn btn-success btn-icon-square-sm">
                                                <a href="{{ route('transfers.edit', $transfer) }}"><i class="las la-pen"></i></a>
                                            </button>
                                        @endif

                                        @if(($slug && (\Illuminate\Support\Facades\Gate::allows("delete {$slug}"))) || \Illuminate\Support\Facades\Gate::allows('delete transfers'))
                                            <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-icon-square-sm" onclick="return confirm('هل أنت متأكد؟')">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif

                                    </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
@endsection

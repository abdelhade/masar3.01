@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.vouchers')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => $currentTypeInfo['title'],
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => $currentTypeInfo['title']],
        ],
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>{{ $currentTypeInfo['title'] }}</h2>

            @php
                // تحديد الصلاحيات المتاحة للإضافة
                $canCreateReceipt = auth()->user()->can('create recipt');
                $canCreatePayment = auth()->user()->can('create payment');
                $canCreateExpPayment = auth()->user()->can('create exp-payment');
                $canCreateMultiPayment = auth()->user()->can('create multi-payment');
                $canCreateMultiReceipt = auth()->user()->can('create multi-receipt');

                $hasAnyCreatePermission = $canCreateReceipt || $canCreatePayment || $canCreateExpPayment ||
                                         $canCreateMultiPayment || $canCreateMultiReceipt;
            @endphp

            @if($hasAnyCreatePermission)
                @if (isset($currentTypeInfo['show_dropdown']) && $currentTypeInfo['show_dropdown'])
                    <div class="dropdown">
                        <button class="btn btn-main dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('Add New Voucher') }}
                        </button>
                        <ul class="dropdown-menu">
                            @if($canCreateReceipt)
                                <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'receipt']) }}">
                                        <i class="fas fa-plus-circle text-success me-2"></i>{{ __('General Receipt Voucher') }}
                                    </a></li>
                            @endif
                            @if($canCreatePayment)
                                <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'payment']) }}">
                                        <i class="fas fa-minus-circle text-danger me-2"></i>{{ __('General Payment Voucher') }}
                                    </a></li>
                            @endif
                            @if($canCreateExpPayment)
                                <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'exp-payment']) }}">
                                        <i class="fas fa-credit-card text-warning me-2"></i>{{ __('Expense Payment Voucher') }}
                                    </a></li>
                            @endif

                            @if(($canCreateReceipt || $canCreatePayment || $canCreateExpPayment) && ($canCreateMultiPayment || $canCreateMultiReceipt))
                                <li><hr class="dropdown-divider"></li>
                            @endif

                            @if($canCreateMultiPayment)
                                <li><a class="dropdown-item"
                                        href="{{ route('multi-vouchers.create', ['type' => 'multi_payment']) }}">
                                        <i class="fas fa-list-alt text-info me-2"></i>{{ __('Multi Payment Voucher') }}
                                    </a></li>
                            @endif
                            @if($canCreateMultiReceipt)
                                <li><a class="dropdown-item"
                                        href="{{ route('multi-vouchers.create', ['type' => 'multi_receipt']) }}">
                                        <i class="fas fa-list-ul text-primary me-2"></i>{{ __('Multi Receipt Voucher') }}
                                    </a></li>
                            @endif
                        </ul>
                    </div>
                @else
                    @if (in_array($type, ['multi_payment', 'multi_receipt']))
                        @if($type === 'multi_payment' && $canCreateMultiPayment)
                            <a href="{{ route('multi-vouchers.create', ['type' => $type]) }}"
                                class="btn btn-{{ $currentTypeInfo['color'] }}">
                                <i class="fas {{ $currentTypeInfo['icon'] }} me-2"></i>
                                {{ $currentTypeInfo['create_text'] }}
                            </a>
                        @elseif($type === 'multi_receipt' && $canCreateMultiReceipt)
                            <a href="{{ route('multi-vouchers.create', ['type' => $type]) }}"
                                class="btn btn-{{ $currentTypeInfo['color'] }}">
                                <i class="fas {{ $currentTypeInfo['icon'] }} me-2"></i>
                                {{ $currentTypeInfo['create_text'] }}
                            </a>
                        @endif
                    @else
                        @if($type === 'receipt' && $canCreateReceipt)
                            <a href="{{ route('vouchers.create', ['type' => $type]) }}"
                                class="btn btn-{{ $currentTypeInfo['color'] }}">
                                <i class="fas {{ $currentTypeInfo['icon'] }} me-2"></i>
                                {{ $currentTypeInfo['create_text'] }}
                            </a>
                        @elseif($type === 'payment' && $canCreatePayment)
                            <a href="{{ route('vouchers.create', ['type' => $type]) }}"
                                class="btn btn-{{ $currentTypeInfo['color'] }}">
                                <i class="fas {{ $currentTypeInfo['icon'] }} me-2"></i>
                                {{ $currentTypeInfo['create_text'] }}
                            </a>
                        @elseif($type === 'exp-payment' && $canCreateExpPayment)
                            <a href="{{ route('vouchers.create', ['type' => $type]) }}"
                                class="btn btn-{{ $currentTypeInfo['color'] }}">
                                <i class="fas {{ $currentTypeInfo['icon'] }} me-2"></i>
                                {{ $currentTypeInfo['create_text'] }}
                            </a>
                        @endif
                    @endif
                @endif
            @endif
        </div>

        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th>{{ __('#') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Operation Number') }}</th>
                            <th>{{ __('Operation Type') }}</th>
                            <th>{{ __('Description') }}</th>
                            @if(isMultiCurrencyEnabled())
                                <th>{{ __('Amount') }} ({{ __('Foreign Currency') }})</th>
                                <th>{{ __('Amount') }} ({{ __('Local Currency') }})</th>
                            @else
                                <th>{{ __('Amount') }}</th>
                            @endif
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Opposite Account') }}</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th>{{ __('Notes') }}</th>
                            <th>{{ __('Review') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $x = 1; @endphp
                        @forelse ($vouchers as $index => $voucher)
                            @php
                                // تحديد الصلاحيات بناءً على نوع السند
                                $canEdit = false;
                                $canDelete = false;

                                switch($voucher->pro_type) {
                                    case 1: // receipt
                                        $canEdit = auth()->user()->can('edit recipt');
                                        $canDelete = auth()->user()->can('delete recipt');
                                        break;
                                    case 2: // payment
                                    case 101: // expense_voucher (payment)
                                        $canEdit = auth()->user()->can('edit payment');
                                        $canDelete = auth()->user()->can('delete payment');
                                        break;
                                    case 3: // exp-payment
                                        $canEdit = auth()->user()->can('edit exp-payment');
                                        $canDelete = auth()->user()->can('delete exp-payment');
                                        break;
                                    case 32: // multi_receipt
                                        $canEdit = auth()->user()->can('edit multi-receipt');
                                        $canDelete = auth()->user()->can('delete multi-receipt');
                                        break;
                                    case 33: // multi_payment
                                        $canEdit = auth()->user()->can('edit multi-payment');
                                        $canDelete = auth()->user()->can('delete multi-payment');
                                        break;
                                }

                                $hasAnyActionPermission = $canEdit || $canDelete;
                            @endphp
                            <tr>
                                <td>{{ $x++ }}</td>
                                <td>{{ $voucher->pro_date }}</td>
                                <td>{{ $voucher->pro_id }}</td>
                                <td>
                                    <span
                                        class="badge
                                        @if ($voucher->pro_type == 1) bg-success
                                        @elseif(in_array($voucher->pro_type, [2, 101])) bg-danger
                                        @elseif($voucher->pro_type == 3) bg-warning
                                        @elseif($voucher->pro_type == 32) bg-primary
                                        @elseif($voucher->pro_type == 33) bg-info
                                        @else bg-secondary @endif">
                                        {{ $voucher->type->ptext ?? __('Not Specified') }}
                                    </span>
                                </td>
                                <td>{{ $voucher->details }}</td>
                                @if(isMultiCurrencyEnabled())
                                    {{-- Column 1: Foreign Currency Amount --}}
                                    <td class="h5 fw-bold">
                                        @if($voucher->currency_id && $voucher->currency_rate > 1)
                                            {{ number_format($voucher->pro_value / $voucher->currency_rate, 2) }}
                                            {{ $voucher->currency?->name ?? '' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    {{-- Column 2: Local Currency Amount (Base Value) --}}
                                    <td class="h5 fw-bold">
                                        {{ number_format($voucher->pro_value, 2) }}
                                    </td>
                                @else
                                    {{-- Single column when multi-currency is disabled --}}
                                    <td class="h5 fw-bold">
                                        {{ number_format($voucher->pro_value, 2) }}
                                    </td>
                                @endif
                                <td>{{ $voucher->account1->aname ?? '' }}</td>
                                <td>{{ $voucher->account2->aname ?? '' }}</td>
                                <td>{{ $voucher->emp1->aname ?? '' }}</td>
                                <td>{{ $voucher->user->name ?? '' }}</td>
                                <td>{{ $voucher->created_at ? $voucher->created_at->format('Y-m-d') : '' }}</td>
                                <td>{{ $voucher->notes ?? '' }}</td>
                                <td>
                                    <span class="badge {{ $voucher->is_approved ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $voucher->is_approved ? __('Yes') : __('No') }}
                                    </span>
                                </td>
                                <td>
                                    @if($hasAnyActionPermission)
                                        <div class="btn-group" role="group">
                                            @if($canEdit)
                                                @if(in_array($voucher->pro_type, [32, 33])) {{-- multi vouchers --}}
                                                    <a href="{{ route('multi-vouchers.edit', $voucher) }}" class="btn btn-sm btn-warning" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-sm btn-warning" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            @if(in_array($voucher->pro_type, [32, 33])) {{-- multi vouchers --}}
                                                @php
                                                    $pname = $voucher->type->pname ?? null;
                                                    $canDuplicate = match($pname) {
                                                        'multi_payment' => auth()->user()->can('create multi-payment'),
                                                        'multi_receipt' => auth()->user()->can('create multi-receipt'),
                                                        default => false,
                                                    };
                                                @endphp
                                                @if($canDuplicate)
                                                    <a href="{{ route('multi-vouchers.duplicate', $voucher) }}" class="btn btn-sm btn-info" title="{{ __('Duplicate Operation') }}">
                                                        <i class="fas fa-copy"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            @if($canDelete)
                                                <form action="{{
                                                    in_array($voucher->pro_type, [32, 33])
                                                    ? route('multi-vouchers.destroy', $voucher->id)
                                                    : route('vouchers.destroy', $voucher->id)
                                                }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger"
                                                        onclick="return confirm('{{ __('Are you sure you want to delete this voucher?') }}')" title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">{{ __('Not Allowed') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">
                                    <div class="alert alert-info py-4 mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>{{ __('No vouchers currently available', ['type' => $currentTypeInfo['title']]) }}</strong>
                                        <br>
                                        <small class="text-muted mt-2 d-block">
                                            {{ __('You can add new voucher using the button above', ['type' => strtolower($currentTypeInfo['create_text'])]) }}
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($vouchers, 'links'))
                <div class="d-flex justify-content-center mt-3">
                    {{ $vouchers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
@endpush

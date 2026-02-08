@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.vouchers')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('vouchers'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('vouchers')]],
    ])
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success cake cake-pulse">
                {{ session('success') }}
            </div>
        @endif
        <div class="card-header">
            @can('create multi-payment')
                @if (request('type') == 'multi_payment')
                    <a href="{{ route('multi-vouchers.create', ['type' => 'multi_payment']) }}" class="btn btn-main">
                        {{ __('Add Payment Voucher') }}
                    </a>
                @endif
            @endcan
            @can('create multi-receipt')
                @if (request('type') == 'multi_receipt')
                    <a href="{{ route('multi-vouchers.create', ['type' => 'multi_receipt']) }}" class="btn btn-main">
                        {{ __('Add Receipt Voucher') }}
                    </a>
                @endif
            @endcan
        </div>

        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">

                        <tr>
                            <th class="font-hold fw-bold font-14 text-center">#</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Date') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Operation Number') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Operation Type') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Statement') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Amount') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('From Account') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('To Account') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Employee') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Employee 2') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('User') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Created At') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Notes') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Reviewed') }}</th>
                            <th class="font-hold fw-bold font-14 text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($multis as $multi)
                            <tr>
                                <td class="font-hold fw-bold font-14 text-center">{{ $loop->iteration }}</td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->pro_date }}</td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->pro_id }}</td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->type->ptext ?? '—' }}
                                </td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->details }}</td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->pro_value }}</td>
                                <td class="font-hold fw-bold font-14 text-center">
                                    {{ $accountsMap[$multi->id]['debit'] ?? ($multi->account1->aname ?? __('Multiple')) }}
                                </td>
                                <td class="font-hold fw-bold font-14 text-center">
                                    {{ $accountsMap[$multi->id]['credit'] ?? ($multi->account2->aname ?? __('Multiple')) }}
                                </td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->emp1->aname ?? '' }}
                                </td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->emp2->aname ?? '' }}
                                </td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $usersMap[$multi->id] ?? ($multi->user?->name ?? $multi->user) }}</td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->created_at }}</td>
                                <td class="font-hold fw-bold font-14 text-center">{{ $multi->info }}</td>
                                <td class="font-hold fw-bold font-14 text-center">
                                    {{ $multi->confirmed ? __('Yes') : __('No') }}</td>
                                <td class="font-hold fw-bold font-14 text-center" x-show="columns[16]">
                                    @php
                                        $pname = $multi->type->pname ?? null;
                                        $editPerm = match($pname) {
                                            'multi_payment' => 'edit multi-payment',
                                            'multi_receipt' => 'edit multi-receipt',
                                            default => null,
                                        };
                                        $deletePerm = match($pname) {
                                            'multi_payment' => 'delete multi-payment',
                                            'multi_receipt' => 'delete multi-receipt',
                                            default => null,
                                        };
                                    @endphp
                                    @if ($editPerm && Auth::user()->can($editPerm))
                                        <a href="{{ route('multi-vouchers.edit', $multi) }}"
                                            class="btn btn-success btn-icon-square-sm"><i class="las la-edit"></i></a>
                                    @endif

                                    @php
                                        $canDuplicate = match($pname) {
                                            'multi_payment' => Auth::user()->can('create multi-payment'),
                                            'multi_receipt' => Auth::user()->can('create multi-receipt'),
                                            default => false,
                                        };
                                    @endphp
                                    @if ($canDuplicate)
                                        <a href="{{ route('multi-vouchers.duplicate', $multi) }}"
                                            class="btn btn-info btn-icon-square-sm" title="{{ __('Copy Operation') }}"><i class="las la-copy"></i></a>
                                    @endif

                                    @if ($deletePerm && Auth::user()->can($deletePerm))
                                        <form action="{{ route('multi-vouchers.destroy', $multi->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-icon-square-sm"
                                                onclick="return confirm('{{ __('Are you sure you want to delete this operation and its associated entry?') }}')">
                                                <i class="las la-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center">
                                    <div class="alert alert-info py-3 mb-0" style="font-size: 1.2rem; font-weight: 500;">
                                        <i class="las la-info-circle me-2"></i>
                                        {{ __('No data available') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
@endsection

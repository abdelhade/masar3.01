@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.purchases-invoices')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Requisitions'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Requisitions')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        {{-- أزرار التصدير --}}
                        <x-table-export-actions table-id="purchase-requests-table" filename="purchase-requests"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />


                        <table id="purchase-requests-table" class="table table-striped mb-0 text-center align-middle"
                            style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Document Number') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    {{-- @canany(['تتبع طلب الاحتياج', 'تأكيد طلب الاحتياج']) --}}
                                    <th>{{ __('Actions') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $req)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $req->pro_id }}</td>
                                        <td>{{ $req->pro_date }}</td>
                                        <td>{{ number_format($req->pro_value, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge
                                                    @if ($req->workflow_state == __('Completed')) bg-success
                                                    @elseif ($req->workflow_state == __('Pending')) bg-warning
                                                    @elseif ($req->workflow_state == __('Rejected')) bg-danger
                                                    @else bg-secondary @endif">
                                                {{ __($req->workflow_state ?? 'Unknown') }}
                                            </span>
                                        </td>


                                        {{-- @canany(['تتبع طلب الاحتياج', 'تأكيد طلب الاحتياج']) --}}
                                        <td>
                                            {{-- @can('تتبع طلب الاحتياج') --}}
                                            <a href="{{ route('invoices.track', ['id' => $req->id]) }}"
                                                class="btn btn-secondary ">
                                                <i class="fas fa-route"></i> {{ __('Track Order Stages') }}
                                            </a>
                                            {{-- @endcan --}}

                                            <button type="button" class="btn btn-info"
                                                onclick='Livewire.dispatch("openManufacturingModal", { items: {{ json_encode($req->operationItems->map(fn($item) => ["id" => $item->item_id, "name" => $item->item->name ?? "Unknown", "qty" => $item->qty_in ?? $item->qty])->values()) }} })'>
                                                <i class="fas fa-industry"></i> {{ __('Manufacturing Details') }}
                                            </button>


                                            {{-- مثال على الموافقة (تقدر تفعلها لما تحتاج) --}}
                                            {{--
                                                @can('تأكيد طلب الاحتياج')
                                                    <form action="{{ route('invoices.confirm', ['id' => $req->id]) }}"
                                                          method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <input type="hidden" name="next_stage" value="1">
                                                        <button class="btn btn-success btn-icon-square-sm">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                                --}}
                                        </td>
                                        {{-- @endcanany --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No requests at the moment') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>


                    <div class="mt-3">
                        {{ $requests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:manufacturing::manufacturing-cost-modal />
@endsection

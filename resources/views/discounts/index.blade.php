@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.discounts')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Discounts'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Discounts')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                @if (is_null($type))
                    <div class="alert alert-warning text-center">
                        {{ __('Please select discount type from the menu.') }}
                    </div>
                @else
                    <h4>
                        @if ($type == 30)
                            {{ __('Allowed Discounts List') }}
                        @elseif ($type == 31)
                            {{ __('Earned Discounts List') }}
                        @else
                            {{ __('All Discounts') }}
                        @endif
                    </h4>

                    <div class="card-body">
                        <div class="table-responsive" style="overflow-x: auto;">

                            <x-table-export-actions table-id="discount-table" filename="discount-table"
                                excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                                print-label="{{ __('Print') }}" />

                            <table id="discount-table" class="table table-striped mb-0 text-center"
                                style="min-width: 1000px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Discount Type') }}</th>
                                        <th>{{ __('Discount Value') }}</th>
                                        <th>{{ __('Document Date') }}</th>
                                        <th>{{ __('Document Number') }}</th>
                                        <th>{{ __('Debit Account') }}</th>
                                        <th>{{ __('Credit Account') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($discounts as $discount)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span
                                                    class="badge
                                                @if ($discount->acc1 == 49 || $discount->acc2 == 49) bg-success text-dark
                                                @elseif($discount->acc1 == 54 || $discount->acc2 == 54)
                                                    bg-warning text-dark
                                                @else
                                                    bg-secondary @endif
                                                text-uppercase">
                                                    @if ($discount->acc1 == 49 || $discount->acc2 == 49)
                                                        {{ __('Allowed Discount') }}
                                                    @elseif($discount->acc1 == 54 || $discount->acc2 == 54)
                                                        {{ __('Earned Discount') }}
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                            </td>
                                            <td>{{ $discount->pro_value }}</td>
                                            <td>{{ \Carbon\Carbon::parse($discount->pro_date)->format('Y-m-d') }}</td>
                                            <td>{{ $discount->pro_id }}</td>
                                            <td>{{ $discount->acc1Head->aname ?? '-' }}</td>
                                            <td>{{ $discount->acc2Head->aname ?? '-' }}</td>
                                            <td>{{ $discount->info }}</td>
                                            @canany(['edit Allowed Discounts', 'delete Allowed Discounts'])
                                                <td>
                                                    @can('edit Allowed Discounts')
                                                        <a href="{{ route('discounts.edit', ['discount' => $discount->id, 'type' => $discount->acc1 == 97 ? 31 : 30]) }}"
                                                            class="btn btn-success btn-sm">
                                                            <i class="las la-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete Allowed Discounts')
                                                        <form action="{{ route('discounts.destroy', $discount->id) }}"
                                                            method="POST" style="display:inline-block;"
                                                            onsubmit="return confirm('{{ __('Are you sure you want to delete?') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="las la-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            @endcanany
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13">
                                                <div class="alert alert-info text-center mb-0">
                                                    {{ __('No data added yet') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
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

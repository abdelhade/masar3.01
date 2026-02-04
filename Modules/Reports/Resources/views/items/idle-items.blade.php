@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Idle Items Report'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Reports'), 'url' => route('reports.index')],
            ['label' => __('Idle Items Report')],
        ],
    ])

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reports.items.idle') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="days" class="form-label">{{ __('Idle Days') }}:</label>
                    <input type="number" name="days" id="days" class="form-control form-control-sm"
                        value="{{ $days ?? 30 }}" min="1" max="365" style="width: 100px;">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <x-table-export-actions table-id="idle-items-table" filename="idle-items"
                    excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                    print-label="{{ __('Print') }}" />

                <table id="idle-items-table" class="table table-striped table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th class="text-end">{{ __('Current Balance') }}</th>
                            <th class="text-end">{{ __('Balance Value') }}</th>
                            <th class="text-end">{{ __('Average Cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php $b = $balances->get($item->id); @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td class="text-end">
                                    <span class="fw-bold {{ $b && $b->balance > 0 ? 'text-success' : 'text-muted' }}">
                                        {{ $b ? number_format((float) $b->balance, 2) : '—' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary">
                                        {{ $b ? number_format((float) $b->balance_value, 2) : '—' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    {{ number_format($item->average_cost ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info mb-0">{{ __('No Idle Items') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

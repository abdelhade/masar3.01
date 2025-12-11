@extends('admin.dashboard')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <a href="{{ route('shipping.zones.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> {{ __('Add Zone') }}
        </a>
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Base Rate') }}</th>
                            <th>{{ __('Rate/KG') }}</th>
                            <th>{{ __('Est. Days') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zones as $zone)
                        <tr>
                            <td>{{ $zone->id }}</td>
                            <td>{{ $zone->name }}</td>
                            <td><span class="badge bg-info">{{ $zone->code }}</span></td>
                            <td>{{ number_format($zone->base_rate, 2) }}</td>
                            <td>{{ number_format($zone->rate_per_kg, 2) }}</td>
                            <td>{{ $zone->estimated_days }} {{ __('days') }}</td>
                            <td>
                                <span class="badge bg-{{ $zone->is_active ? 'success' : 'danger' }}">
                                    {{ $zone->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shipping.zones.edit', $zone->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('shipping.zones.destroy', $zone->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $zones->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

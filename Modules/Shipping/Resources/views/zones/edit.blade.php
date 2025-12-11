@extends('admin.dashboard')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('shipping.zones.update', $zone->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label>{{ __('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ $zone->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Code') }}</label>
                        <input type="text" name="code" class="form-control" value="{{ $zone->code }}" required>
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="3">{{ $zone->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>{{ __('Base Rate') }}</label>
                            <input type="number" step="0.01" name="base_rate" class="form-control" value="{{ $zone->base_rate }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>{{ __('Rate per KG') }}</label>
                            <input type="number" step="0.01" name="rate_per_kg" class="form-control" value="{{ $zone->rate_per_kg }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Estimated Days') }}</label>
                        <input type="number" name="estimated_days" class="form-control" value="{{ $zone->estimated_days }}" required>
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Branch') }}</label>
                        <select name="branch_id" class="form-control" required>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $zone->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $zone->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Active') }}</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    <a href="{{ route('shipping.zones.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

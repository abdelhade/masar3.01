@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Activities'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Activities'), 'url' => route('activities.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Activity') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('activities.update', $activity->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">

                            {{-- Activity Title --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="title">{{ __('Activity Title') }}</label>
                                <input type="text" class="form-control" id="title" name="title"
                                    placeholder="{{ __('Enter activity title') }}"
                                    value="{{ old('title', $activity->title) }}">
                                @error('title')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Activity Type --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="type">{{ __('Type') }}</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="0" {{ old('type', $activity->type) == 0 ? 'selected' : '' }}>
                                        {{ __('Call') }}</option>
                                    <option value="1" {{ old('type', $activity->type) == 1 ? 'selected' : '' }}>
                                        {{ __('Message') }}</option>
                                    <option value="2" {{ old('type', $activity->type) == 2 ? 'selected' : '' }}>
                                        {{ __('Meeting') }}</option>
                                </select>
                                @error('type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Activity Date --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="activity_date">{{ __('Activity Date') }}</label>
                                <input type="date" class="form-control" id="activity_date" name="activity_date"
                                    value="{{ old('activity_date', $activity->activity_date?->format('Y-m-d')) }}">
                                @error('activity_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Scheduled Time --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="scheduled_at">{{ __('Time') }}</label>
                                <input type="time" class="form-control" id="scheduled_at" name="scheduled_at"
                                    value="{{ old('scheduled_at', $activity->scheduled_at?->format('H:i')) }}">
                                @error('scheduled_at')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Client --}}
                            <div class="col-md-3 mb-3">
                                <x-dynamic-search name="client_id" :label="__('Client')" column="cname"
                                    model="App\Models\Client" :placeholder="__('Search for client...')" :required="false" :class="'form-select'"
                                    :selected="$activity->client_id" />
                            </div>

                            {{-- Assigned To --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="assigned_to">{{ __('Assigned To') }}</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">{{ __('Select employee') }}</option>
                                    @foreach ($users as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ old('assigned_to', $activity->assigned_to) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="description">{{ __('Description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="{{ __('Enter activity details') }}">{{ old('description', $activity->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('Save Changes') }}
                            </button>

                            <a href="{{ route('activities.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Manufacturing Stages'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Manufacturing Stages'), 'url' => route('manufacturing.stages.index')],
            ['label' => __('Show')],
        ],
    ])

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">{{ __('Manufacturing Stage Details') }}</h2>
                    <a href="{{ route('manufacturing.stages.index') }}" class="btn btn-light btn-sm">
                        <i class="las la-arrow-right"></i> {{ __('Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">{{ __('Stage Name') }}:</label>
                            <p class="mb-0">{{ $manufacturingStage->name }}</p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">{{ __('Order') }}:</label>
                            <p class="mb-0">{{ (int) $manufacturingStage->order }}</p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">{{ __('Branch') }}:</label>
                            <p class="mb-0">{{ optional($manufacturingStage->branch)->name ?? '—' }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">{{ __('Estimated Duration (hours)') }}:</label>
                            <p class="mb-0">{{ $manufacturingStage->estimated_duration ?? '—' }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">{{ __('Cost (EGP)') }}:</label>
                            <p class="mb-0">{{ number_format($manufacturingStage->cost, 2) }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">{{ __('Status') }}:</label>
                            @if ($manufacturingStage->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold">{{ __('Description') }}:</label>
                        <div class="border rounded p-3" style="background: #f9f9f9;">
                            {!! $manufacturingStage->description
                                ? nl2br(e($manufacturingStage->description))
                                : '<span class="text-muted">' . __('No description available') . '</span>' !!}
                        </div>
                    </div>

                    <div class="d-flex justify-content-start gap-2">
                        @can('edit Manufacturing Stages')
                            <a href="{{ route('manufacturing.stages.edit', $manufacturingStage->id) }}"
                                class="btn btn-success">
                                <i class="las la-edit"></i> {{ __('Edit') }}
                            </a>
                        @endcan

                        @can('delete Manufacturing Stages')
                            <form action="{{ route('manufacturing.stages.destroy', $manufacturingStage->id) }}" method="POST"
                                onsubmit="return confirm('{{ __('Are you sure you want to delete this stage?') }}');"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="las la-trash"></i> {{ __('Delete') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="card-footer text-muted text-end">
                    <small>{{ __('Created at') }}: {{ $manufacturingStage->created_at->format('Y-m-d H:i') }} |
                        {{ __('Last updated') }}: {{ $manufacturingStage->updated_at->format('Y-m-d H:i') }}</small>
                </div>
            </div>
        </div>
    </div>
@endsection

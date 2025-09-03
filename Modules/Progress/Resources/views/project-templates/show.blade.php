@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('قوالب المشاريع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('قوالب المشاريع'), 'url' => route('project.template.index')],
            ['label' => __('عرض القالب')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">{{ $projectTemplate->name }}</h2>
                </div>
                <div class="card-body">
                    <!-- الوصف -->
                    <p class="mb-3">
                        <strong class="text-dark">{{ __('general.description') }}:</strong>
                        <span class="text-muted">{{ $projectTemplate->description ?: '—' }}</span>
                    </p>

                    <hr>

                    <!-- العناصر -->
                    <h6 class="fw-semibold mb-3 text-secondary">{{ __('general.template_items') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('general.item_name') }}</th>
                                    <th>{{ __('general.unit') }}</th>
                                    <th>{{ __('general.default_quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($projectTemplate->items as $i => $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $i + 1 }}</td>
                                        <td>{{ $item->workItem->name ?? '—' }}</td>
                                        <td>{{ $item->workItem->unit ?? '—' }}</td>
                                        <td>{{ $item->default_quantity ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            {{ __('general.no_items') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('project.template.index') }}" class="btn btn-danger">
                            <i class="las la-arrow-right"></i> {{ __('general.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

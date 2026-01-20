@extends('progress::layouts.daily-progress')

{{-- Sidebar is now handled by the layout itself --}}

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.work_item_categories'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('general.work_item_categories')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">

            {{-- زرار الإضافة --}}
            @can('create progress-work-item-categories')
            <a href="{{ route('work-item-categories.create') }}" type="button" class="btn btn-primary font-hold fw-bold">
                {{ __('general.add_work_item_category') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            @endcan

            <br><br>
            {{-- الجدول --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">


                        <table id="work-item-categories-table" class="table table-striped mb-0" style="min-width: 800px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('general.name') }}</th>
                                    <th>{{ __('general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            @can('edit progress-work-item-categories')
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('work-item-categories.edit', $category->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete progress-work-item-categories')
                                            <form action="{{ route('work-item-categories.destroy', $category->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('{{ __('general.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('general.no_work_item_categories') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

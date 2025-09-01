@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.work_items_management'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('general.work_items_management')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">

            {{-- زرار الإضافة --}}
            {{-- @can('project-types-create') --}}
            <a href="{{ route('work.items.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                {{ __('general.new_work_item') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}

            <br><br>
            {{-- الجدول --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="project-types-table" filename="project-types"
                            excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="project-types-table" class="table table-striped mb-0" style="min-width: 800px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('general.item_name') }}</th>
                                    <th>{{ __('general.unit_of_measurement') }}</th>
                                    <th>{{ __('general.description') }}</th>
                                    {{-- @canany(['project-types-edit', 'project-types-delete']) --}}
                                    <th>{{ __('general.actions') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workItems as $workItem)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $workItem->name }}</td>
                                        <td>{{ $workItem->unit }}</td>
                                        <td>{{ Str::limit($workItem->description, 50) }}</td> {{-- @canany(['project-types-edit', 'project-types-delete']) --}}
                                        <td>
                                            {{-- @can('project-types-edit') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('work.items.edit', $workItem->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan

                                                @can('project-types-delete') --}}
                                            <form action="{{ route('work.items.destroy', $workItem->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('{{ __('general.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                            {{-- @endcan --}}
                                        </td>
                                        {{-- @endcanany --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('general.no_project_types') }}
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

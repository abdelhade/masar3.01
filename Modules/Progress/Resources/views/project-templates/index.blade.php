@extends('progress::layouts.daily-progress')

{{-- Sidebar is now handled by the layout itself --}}
@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.project_templates'),
        'items' => [['label' => __('general.home'), 'url' => route('admin.dashboard')], ['label' => __('general.project_templates')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create progress-project-templates') 
            <a href="{{ route('project.template.create') }}" type="button" class="btn btn-primary font-hold fw-bold">
                {{ __('general.new_template') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                       
                        <table id="projects-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('general.template_name') }}</th>
                                    <th>{{ __('general.project_type') }}</th>
                                    <th>{{ __('general.items_count') }}</th>
                                    <th>{{ __('general.created_at') }}</th>
                                
                                     @canany(['edit progress-project-templates', 'delete progress-project-templates']) 
                                    <th>{{ __('general.actions') }}</th>
                                    @endcanany 
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($templates as $template)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->projectType->name ?? '-' }}</td>
                                        <td><span class="badge bg-info">{{ $template->items_count }}</span></td>
                                        <td>{{ $template->created_at->format('Y-m-d') }}</td>
                                        @canany(['edit progress-project-templates', 'delete progress-project-templates' , 'view progress-project-templates', 'view progress-project-templates']) 
                                        <td>
                                            @can('view progress-project-templates') 
                                            <a href="{{ route('project.template.show', $template->id) }}"
                                                class="btn btn-primary btn-icon-square-sm" title="{{ __('general.view') }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                             @endcan
                                             @can('edit progress-project-templates')
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('project.template.edit', $template->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            @endcan
                                                @can('delete progress-project-templates') 
                                            <form action="{{ route('project.template.destroy', $template->id) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('{{ __('general.confirm_delete_template') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                             @endcan
                                        </td>
                                         @endcanany 
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('general.no_data') }}
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

@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.manufacturing')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h4 class="mb-0">
                            <i class="las la-file-invoice"></i>
                            {{ __('Manufacturing Invoice Templates') }}
                        </h4>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($templates->isEmpty())
                            <div class="alert alert-info text-center">
                                <i class="las la-info-circle" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-2">{{ __('No templates found for manufacturing invoices') }}</p>
                                <p class="text-muted small">{{ __('You can create templates from the manufacturing invoice creation page') }}</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead style="background: #f8f9fa;">
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 20%;">{{ __('Template Name') }}</th>
                                            <th style="width: 10%;">{{ __('Invoice ID') }}</th>
                                            <th style="width: 15%;">{{ __('Date') }}</th>
                                            <th style="width: 15%;">{{ __('Total Cost') }}</th>
                                            <th style="width: 15%;">{{ __('Expected Time') }}</th>
                                            <th style="width: 10%;">{{ __('Status') }}</th>
                                            <th style="width: 10%;">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($templates as $template)
                                            <tr>
                                                <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}</td>
                                                <td>
                                                    <strong>{{ $template->info }}</strong>
                                                </td>
                                                <td>
                                                    <code>{{ $template->pro_id }}</code>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $template->pro_date }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">{{ number_format($template->pro_value, 2) }}</span>
                                                </td>
                                                <td>
                                                    @if($template->expected_time)
                                                        <span class="badge bg-info">{{ $template->expected_time }} {{ __('hours') }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <form action="{{ route('manufacturing.templates.toggle-active', $template->id) }}" 
                                                          method="POST" 
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $template->is_manager ? 'btn-success' : 'btn-secondary' }}"
                                                                title="{{ $template->is_manager ? __('Active') : __('Inactive') }}">
                                                            <i class="las {{ $template->is_manager ? 'la-check-circle' : 'la-times-circle' }}"></i>
                                                            {{ $template->is_manager ? __('Active') : __('Inactive') }}
                                                        </button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <form action="{{ route('manufacturing.templates.destroy', $template->id) }}" 
                                                              method="POST" 
                                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this template?') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-danger"
                                                                    title="{{ __('Delete') }}">
                                                                <i class="las la-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $templates->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.sales-invoices')
    @include('components.sidebar.purchases-invoices')
    @include('components.sidebar.inventory-invoices')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Invoice Templates'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Invoice Templates')],
        ],
    ])

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Invoice Templates') }}</h3>
            @can('create Invoice Templates')
                <a href="{{ route('invoice-templates.create') }}" class="btn btn-primary btn-sm float-end">
                    <i class="fas fa-plus"></i> {{ __('Add New Template') }}
                </a>
            @endcan
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Invoice Types') }}</th>
                        <th>{{ __('Number of Columns') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Order') }}</th>
                        @canany(['edit Invoice Templates', 'delete Invoice Templates'])
                            <th>{{ __('Actions') }}</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templates as $template)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $template->name }}</td>
                            <td><code>{{ $template->code }}</code></td>
                            <td>{{ Str::limit($template->description, 60) }}</td>
                            <td>
                                @foreach ($template->invoiceTypes as $type)
                                    <span class="badge bg-info">
                                        {{ Modules\Invoices\Models\InvoiceTemplate::getInvoiceTypeName($type->invoice_type) }}
                                        @if ($type->is_default)
                                            <i class="fas fa-star text-warning"></i>
                                        @endif
                                    </span>
                                @endforeach
                            </td>
                            <td>{{ count($template->visible_columns) }}</td>
                            <td>
                                @can('edit Invoice Templates')
                                    <form action="{{ route('invoice-templates.toggle-active', $template) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm btn-{{ $template->is_active ? 'success' : 'secondary' }}">
                                            {{ $template->is_active ? __('Active') : __('Inactive') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                        {{ $template->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                @endcan
                            </td>
                            <td>{{ $template->sort_order }}</td>
                            @canany(['edit Invoice Templates', 'delete Invoice Templates'])
                                <td>
                                    @can('edit Invoice Templates')
                                        <a href="{{ route('invoice-templates.edit', $template) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('delete Invoice Templates')
                                        <form action="{{ route('invoice-templates.destroy', $template) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('{{ __('Are you sure?') }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('No templates available') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $templates->links() }}
        </div>
    </div>
@endsection

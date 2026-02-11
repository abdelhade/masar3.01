@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.settings')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Branches'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Branches')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create Branches')
                <a href="{{ route('branches.create') }}" type="button" class="btn btn-primary font-hold fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="branches-table" filename="branches-table"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="branches-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Branch Code') }}</th>
                                    <th>{{ __('Address') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @canany(['edit Branches', 'delete Branches'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $branch)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->code ?? '-' }}</td>
                                        <td>{{ $branch->address ?? '-' }}</td>
                                        <td class="text-center align-middle form-switch">
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <input type="checkbox" class="form-check-input branch-status-switch p-0 m-0"
                                                    style="width: 2.2rem; height: 1.2rem;" data-id="{{ $branch->id }}"
                                                    id="branchSwitch{{ $branch->id }}"
                                                    {{ $branch->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>


                                        @canany(['edit Branches', 'delete Branches'])
                                            <td>
                                                @can('edit Branches')
                                                    <a class="btn btn-primary btn-icon-square-sm"
                                                        href="{{ route('branches.show', $branch->id) }}">
                                                        <i class="las la-eye"></i>
                                                    </a>

                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('branches.edit', $branch->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Branches')
                                                    <form action="{{ route('branches.destroy', $branch->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this branch?') }}');">
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
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No data available') }}
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switches = document.querySelectorAll('.branch-status-switch');

            switches.forEach(switchInput => {
                switchInput.addEventListener('change', function() {
                    const branchId = this.dataset.id;
                    const status = this.checked ? 1 : 0;

                    fetch("{{ route('branches.toggleStatus') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                id: branchId,
                                is_active: status
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                // ارجع الحالة القديمة لو فشل
                                this.checked = !this.checked;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'حدث خطأ',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            this.checked = !this.checked;
                            Swal.fire({
                                icon: 'error',
                                title: 'حدث خطأ',
                                toast: true,
                                position: 'top-end',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        });
                });
            });
        });
    </script>
@endpush

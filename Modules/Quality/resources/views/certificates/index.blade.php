@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0"><i class="fas fa-certificate me-2"></i>{{ __("Certificates") }}</h2>
                    </div>
                    <div>
                        @can('create certificates')
                            <a href="{{ route('quality.certificates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>{{ __("New Certificate") }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("Active Certificates") }}</h6>
                        <h3 class="text-success">{{ $stats['active'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("Expiring Soon") }}</h6>
                        <h3 class="text-warning">{{ $stats['expiring_soon'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("Expired") }}</h6>
                        <h3 class="text-danger">{{ $stats['expired'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("Total") }}</h6>
                        <h3>{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __("Certificate Number") }}</th>
                                <th>{{ __("Certificate Name") }}</th>
                                <th>{{ __("Type") }}</th>
                                <th>{{ __("Issuing Authority") }}</th>
                                <th>{{ __("Issue Date") }}</th>
                                <th>{{ __("Valid Until") }}</th>
                                <th>{{ __("Days Remaining") }}</th>
                                <th>{{ __("Status") }}</th>
                                @canany(['edit certificates', 'delete certificates', 'view certificates'])
                                    <th>{{ __("Actions") }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($certificates as $certificate)
                                <tr
                                    class="{{ $certificate->isExpired() ? 'table-danger' : ($certificate->isExpiringSoon() ? 'table-warning' : '') }}">
                                    <td><strong>{{ $certificate->certificate_number }}</strong></td>
                                    <td>{{ $certificate->certificate_name }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            شهادة
                                        </span>
                                    </td>
                                    <td>{{ $certificate->issuing_authority }}</td>
                                    <td>{{ $certificate->issue_date->format('Y-m-d') }}</td>
                                    <td>{{ $certificate->expiry_date->format('Y-m-d') }}</td>
                                    <td>
                                        @php
                                            $daysLeft = $certificate->daysUntilExpiry();
                                        @endphp
                                        <span
                                            class="badge bg-{{ $daysLeft < 0 ? 'danger' : ($daysLeft < 30 ? 'warning' : 'success') }}">
                                            {{ abs($daysLeft) }} {{ $daysLeft < 0 ? __("Expired") : __("Days") }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ match ($certificate->status) {
                                                'active' => 'success',
                                                'expired' => 'danger',
                                                'renewal_pending' => 'warning',
                                                default => 'secondary',
                                            } }}">
                                            {{ $certificate->status }}
                                        </span>
                                    </td>
                                    @canany(['edit certificates', 'delete certificates', 'view certificates'])
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view certificates')
                                                    <a href="{{ route('quality.certificates.show', $certificate) }}"
                                                        class="btn btn-sm btn-info" title="{{ __("View") }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('edit certificates')
                                                    <a href="{{ route('quality.certificates.edit', $certificate) }}"
                                                        class="btn btn-sm btn-warning" title="{{ __("Edit") }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete certificates')
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $certificate->id }}" title="{{ __("Delete") }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal{{ $certificate->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{ __("Are you sure you want to delete certificate") }} "{{ $certificate->certificate_name }}"?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                                                            <form
                                                                action="{{ route('quality.certificates.destroy', $certificate) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">{{ __("Delete") }}</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">{{ __("No certificates") }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($certificates->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $certificates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@extends('admin.dashboard')

@section('title', __('projects.create'))

@section('content')
    <style>
        :root {
            --primary-color: #2c7be5;
            --success-color: #28a745;
            --card-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        }

        .main-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            margin-top: 2rem;
        }

        .card-header {
            border-radius: 0.75rem 0.75rem 0 0 !important;
            padding: 1.2rem 1.5rem;
            background: linear-gradient(120deg, #28a745 0%, #1e7e34 100%) !important;
            border: none;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #344050;
        }

        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e3ebf6;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(44, 123, 229, 0.15);
        }

        .input-group-text {
            background-color: #f5f7f9;
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid #e3ebf6;
        }

        .employee-list {
            max-height: 220px;
            overflow-y: auto;
            border: 1px solid #e3ebf6;
            border-radius: 0.5rem;
            background: #f8f9fa;
            padding: 0.75rem;
        }

        .form-check {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }

        .form-check:last-child {
            border-bottom: none;
        }

        .form-check:hover {
            background-color: #f1f3f5;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }

        .btn-secondary {
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
    </style>

    <div class="container">
        <div class="main-card card">
            <div class="card-header text-white">
                <h5 class="mb-0"><i class="fas fa-folder-plus me-2"></i> {{ __('projects.create') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('progress.projcet.store') }}" method="POST" id="createProjectForm">
                    @csrf
                    @include('progress::projects._form', ['workItems' => $workItems])

                    <div class="mb-4">
                        <label for="working_zone" class="form-label">{{ __('general.working_zone') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" id="working_zone" name="working_zone" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ __('general.employees') }}</label>
                        <div class="employee-list">
                            @foreach ($employees as $employee)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="employees[]"
                                        id="employee_{{ $employee->id }}" value="{{ $employee->id }}">
                                    <label class="form-check-label" for="employee_{{ $employee->id }}">
                                        {{ $employee->name }}
                                        @if ($employee->position)
                                            <small class="text-muted">({{ $employee->position }})</small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">{{ __('general.select_multiple_by_clicking') }}</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ __('general.save') }}
                        </button>
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> {{ __('general.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('createProjectForm').addEventListener('submit', function(e) {
            const checkedEmployees = document.querySelectorAll('input[name="employees[]"]:checked');
            if (checkedEmployees.length === 0) {
                e.preventDefault();
                alert('{{ __('general.select_at_least_one_employee') }}');
            }
        });
    </script>
@endsection

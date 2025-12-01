<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function employees(): LengthAwarePaginator
    {
        return Employee::with([
            'department:id,title',
            'job:id,title',
            'shift:id,name,start_time,end_time'
        ])
            ->select('id', 'name', 'email', 'phone', 'status', 'department_id', 'job_id', 'shift_id', 'branch_id')
            ->when($this->search, function ($q) {
                // Optimize search: use prefix search when possible (can use index)
                $search = trim($this->search);
                if (strlen($search) > 0) {
                    $q->where('name', 'like', "%{$search}%");
                }
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function delete(int $id): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('delete Hr-Employees'), 403, __('hr.unauthorized_action'));

        // Rate limiting check
        $this->ensureIsNotRateLimited('delete');

        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();

            // Clear rate limiter on success
            RateLimiter::clear($this->throttleKey('delete'));

            session()->flash('success', __('hr.employee_deleted'));
        } catch (\Throwable $th) {
            Log::error('Employee delete error', [
                'user_id' => auth()->id(),
                'employee_id' => $id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            session()->flash('error', __('hr.error_occurred'));
        }
    }

    /**
     * Ensure request is not rate limited
     */
    protected function ensureIsNotRateLimited(string $action): void
    {
        $key = $this->throttleKey($action);
        $maxAttempts = 5; // Stricter limit for delete
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw new \Illuminate\Http\Exceptions\ThrottleRequestsException(
                __('hr.rate_limit_exceeded', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)])
            );
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Get throttle key for rate limiting
     */
    protected function throttleKey(string $action): string
    {
        return Str::transliterate(Str::lower(auth()->id() . '|' . $action . '|' . request()->ip()));
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="row">
        @if (session()->has('success'))
            <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('error') }}
            </div>
        @endif

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @can('create Hr-Employees')
                        <a href="{{ route('employees.create') }}"
                            class="btn btn-main font-hold fw-bold position-relative"
                            x-data="{ loading: false }"
                            @click.prevent="loading = true; window.location.href = '{{ route('employees.create') }}';"
                            :disabled="loading">
                            <template x-if="!loading">
                                <span>
                                    {{ __('hr.add_employee') }}
                                    <i class="fas fa-plus me-2"></i>
                                </span>
                            </template>
                            <template x-if="loading">
                                <span>
                                    <span class="spinner-border spinner-border-sm align-middle ms-2" role="status" aria-hidden="true"></span>
                                    {{ __('hr.loading') }}
                                </span>
                            </template>
                        </a>
                    @endcan
                    <input type="text" wire:model.live.debounce.500ms="search" class="form-control w-auto"
                        style="min-width:200px" placeholder="{{ __('hr.search_by_name') }}"
                        aria-label="{{ __('hr.search_by_name') }}">
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                            <x-table-export-actions table-id="employee-table" filename="employee-table"
                                excel-label="{{ __('hr.export_excel') }}" pdf-label="{{ __('hr.export_pdf') }}" print-label="{{ __('hr.print') }}" />

                            <table id="employee-table" class="table table-striped text-center mb-0"
                                style="min-width: 1200px;">
                                <thead class="table-light align-middle">
                                    <tr>
                                        <th class="font-hold fw-bold">#</th>
                                        <th class="font-hold fw-bold">{{ __('hr.name') }}</th>
                                        <th class="font-hold fw-bold">{{ __('hr.email') }}</th>
                                        <th class="font-hold fw-bold">{{ __('hr.phone') }}</th>
                                        <th class="font-hold fw-bold">{{ __('hr.department') }}</th>
                                        <th class="font-hold fw-bold">{{ __('hr.job') }}</th>
                                        <th class="font-hold fw-bold">{{ __('hr.status') }}</th>
                                        @canany(['edit Hr-Employees', 'delete Hr-Employees', 'view Hr-Employees'])
                                            <th class="font-hold fw-bold">{{ __('hr.actions') }}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($this->employees as $employee)
                                        <tr wire:key="employee-{{ $employee->id }}">
                                            <td class="font-hold fw-bold">{{ $loop->iteration + ($this->employees->currentPage() - 1) * $this->employees->perPage() }}</td>
                                            <td class="font-hold fw-bold">{{ $employee->name }}</td>
                                            <td class="font-hold fw-bold">{{ $employee->email }}</td>
                                            <td class="font-hold fw-bold">{{ $employee->phone }}</td>
                                            <td class="font-hold fw-bold">
                                                {{ optional($employee->department)->title }}</td>
                                            <td class="font-hold fw-bold">{{ optional($employee->job)->title }}
                                            </td>
                                            <td class="font-hold fw-bold">{{ $employee->status }}</td>

                                            @canany(['edit Hr-Employees', 'delete Hr-Employees', 'view Hr-Employees'])
                                                <td>
                                                    @can('view Hr-Employees')
                                                        <a href="{{ route('employees.show', $employee->id) }}"
                                                            class="btn btn-info btn-sm me-1"
                                                            title="{{ __('hr.view') }}"
                                                            x-data="{ loadingView{{ $employee->id }}: false }"
                                                            @click.prevent="if (!loadingView{{ $employee->id }}) { loadingView{{ $employee->id }} = true; window.location.href='{{ route('employees.show', $employee->id) }}'; }">
                                                            <span x-show="!loadingView{{ $employee->id }}">
                                                                <i class="las la-eye fa-lg"></i>
                                                            </span>
                                                            <span x-show="loadingView{{ $employee->id }}" style="display: none;">
                                                                <i class="fas fa-spinner fa-spin fa-lg"></i>
                                                            </span>
                                                        </a>
                                                    @endcan
                                                    @can('edit Hr-Employees')
                                                        <a href="{{ route('employees.edit', $employee->id) }}"
                                                            class="btn btn-success btn-sm me-1"
                                                            title="{{ __('hr.edit') }}"
                                                            x-data="{ loadingEdit{{ $employee->id }}: false }"
                                                            @click.prevent="if (!loadingEdit{{ $employee->id }}) { loadingEdit{{ $employee->id }} = true; window.location.href='{{ route('employees.edit', $employee->id) }}'; }">
                                                            <span x-show="!loadingEdit{{ $employee->id }}">
                                                                <i class="las la-edit fa-lg"></i>
                                                            </span>
                                                            <span x-show="loadingEdit{{ $employee->id }}" style="display: none;">
                                                                <i class="fas fa-spinner fa-spin fa-lg"></i>
                                                            </span>
                                                        </a>
                                                    @endcan
                                                    @can('delete Hr-Employees')
                                                        <button 
                                                            type="button"
                                                            class="btn btn-danger btn-sm"
                                                            wire:click="delete({{ $employee->id }})"
                                                            wire:confirm="{{ __('hr.confirm_delete_employee') }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="delete({{ $employee->id }})"
                                                            title="{{ __('hr.delete') }}">
                                                            <span wire:loading.remove wire:target="delete({{ $employee->id }})">
                                                                <i class="las la-trash fa-lg"></i>
                                                            </span>
                                                            <span wire:loading wire:target="delete({{ $employee->id }})">
                                                                <i class="fas fa-spinner fa-spin fa-lg"></i>
                                                            </span>
                                                        </button>
                                                    @endcan
                                                </td>
                                            @endcanany
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->canany(['edit Hr-Employees', 'delete Hr-Employees', 'view Hr-Employees']) ? '8' : '7' }}" 
                                                class="text-center font-hold fw-bold py-4">
                                                <div class="alert alert-info mb-0">
                                                    <i class="las la-info-circle me-2"></i>
                                                    {{ __('hr.no_employees_found') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $this->employees->links() }}
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>


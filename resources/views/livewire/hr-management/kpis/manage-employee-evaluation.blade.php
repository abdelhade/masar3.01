<?php

use Livewire\Volt\Component;
use App\Models\Employee;
use App\Models\Employee_Evaluation;
use App\Models\Kpi;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    public $employee_id;
    public $evaluation_date;
    public $direct_manager;
    public $job_title;
    public $department;
    public $evaluation_period_from;
    public $evaluation_period_to;
    public $total_score = 0;
    public $final_rating = '';
    public $selectedEvaluation = null;
    public $search = '';
    public $showDeleteModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $evaluationId;
    public $viewEvaluation = null;

    // KPI Scores (1-5)
    public $scores = [];
    public $notes = [];
    public function mount()
    {
        $this->evaluation_date = now()->format('Y-m-d');
        $this->evaluation_period_from = now()->startOfMonth()->format('Y-m-d');
        $this->evaluation_period_to = now()->endOfMonth()->format('Y-m-d');
    }

    public function with(): array
    {
        return [
            'employees' => Employee::where('name', 'like', '%' . $this->search . '%')->get(),
            'evaluations' => Employee_Evaluation::with(['employee', 'kpis'])
                ->when($this->search, function ($query) {
                    $query->whereHas('employee', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->latest()
                ->paginate(10),
            'kpis' => Kpi::all(),
        ];
    }

    public function calculateTotalScore()
    {
        $this->total_score = array_sum($this->scores);
        $this->calculateFinalRating();
    }

    public function calculateFinalRating()
    {
        $this->final_rating = match (true) {
            $this->total_score >= 60 => __('ممتاز'),
            $this->total_score >= 50 => __('جيد جدا'),
            $this->total_score >= 40 => __('جيد'),
            $this->total_score >= 30 => __('مقبول'),
            $this->total_score >= 20 => __('ضعيف'),
            default => __('ضعيف'),
        };
    }

    public function loadEmployeeDetails()
    {
        if ($this->employee_id) {
            $employee = Employee::with('job', 'department')->find($this->employee_id);
            if ($employee) {
                $this->job_title = $employee->job?->title;
                $this->department = $employee->department?->title;
            }
        } else {
            $this->job_title = '';
            $this->department = '';
        }
    }

    public function save()
    {
        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'evaluation_date' => 'required|date',
            'evaluation_period_from' => 'required|date',
            'evaluation_period_to' => 'required|date|after:evaluation_period_from',
            'direct_manager' => 'nullable|string|max:255',
            'scores.*' => 'required|integer|min:1|max:50',
        ]);

        $evaluation = Employee_Evaluation::create([
            'employee_id' => $this->employee_id,
            'evaluation_date' => $this->evaluation_date,
            'direct_manager' => $this->direct_manager ?? '',
            'evaluation_period_from' => $this->evaluation_period_from,
            'evaluation_period_to' => $this->evaluation_period_to,
            'total_score' => $this->total_score,
            'final_rating' => $this->final_rating,
        ]);
        foreach ($this->scores as $kpi_id => $score) {
            $evaluation->kpis()->attach($kpi_id, ['score' => $score, 'notes' => $this->notes[$kpi_id] ?? '']);
        }
        session()->flash('message', __('تم حفظ التقييم بنجاح'));
        $this->dispatch('hide-evaluation-modal');
        $this->reset();
    }

    public function edit($id)
    {
        $this->evaluationId = $id;
        $evaluation = Employee_Evaluation::with(['kpis'])->find($id);
        $this->employee_id = $evaluation->employee_id;
        $this->evaluation_date = $evaluation->evaluation_date ? Carbon::parse($evaluation->evaluation_date)->format('Y-m-d') : '';
        $this->direct_manager = $evaluation->direct_manager;
        $this->evaluation_period_from = $evaluation->evaluation_period_from ? Carbon::parse($evaluation->evaluation_period_from)->format('Y-m-d') : '';
        $this->evaluation_period_to = $evaluation->evaluation_period_to ? Carbon::parse($evaluation->evaluation_period_to)->format('Y-m-d') : '';
        foreach ($evaluation->kpis as $kpi) {
            $this->scores[$kpi->id] = $kpi->pivot->score ?? 0;
            $this->notes[$kpi->id] = $kpi->pivot->notes ? $kpi->pivot->notes : '';
        }
        $this->loadEmployeeDetails();
        $this->calculateTotalScore();
        $this->showEditModal = true;
        $this->dispatch('show-evaluation-modal');
    }

    public function update()
    {
        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'evaluation_date' => 'required|date',
            'evaluation_period_from' => 'required|date',
            'evaluation_period_to' => 'required|date|after:evaluation_period_from',
            'direct_manager' => 'nullable|string|max:255',
            'scores.*' => 'required|integer|min:1|max:50',
        ]);

        $evaluation = Employee_Evaluation::find($this->evaluationId);
        $evaluation->update([
            'employee_id' => $this->employee_id,
            'evaluation_date' => $this->evaluation_date,
            'direct_manager' => $this->direct_manager ?? '',
            'evaluation_period_from' => $this->evaluation_period_from,
            'evaluation_period_to' => $this->evaluation_period_to,
            'total_score' => $this->total_score,
            'final_rating' => $this->final_rating,
        ]);
        $evaluation->kpis()->detach();
        foreach ($this->scores as $kpi_id => $score) {
            $evaluation->kpis()->attach($kpi_id, ['score' => $score, 'notes' => $this->notes[$kpi_id] ?? '']);
        }

        $this->showEditModal = false;
        $this->reset();
        session()->flash('message', __('تم تحديث التقييم بنجاح'));
        $this->dispatch('hide-evaluation-modal');
    }

    public function confirmDelete($id)
    {
        $this->evaluationId = $id;
        $this->showDeleteModal = true;
        $this->dispatch('show-delete-modal');
    }

    public function delete()
    {
        $evaluation = Employee_Evaluation::find($this->evaluationId);
        $evaluation->kpis()->detach();
        $evaluation->delete();

        $this->showDeleteModal = false;
        session()->flash('message', __('تم حذف التقييم بنجاح'));
        $this->dispatch('hide-delete-modal');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->dispatch('hide-delete-modal');
    }

    public function view($id)
    {
        $this->viewEvaluation = Employee_Evaluation::with(['employee', 'kpis'])->find($id);
        $this->showViewModal = true;
        $this->dispatch('show-view-modal');
    }

    public function closeView()
    {
        $this->showViewModal = false;
        $this->viewEvaluation = null;
        $this->dispatch('hide-view-modal');
    }
}; ?>
<div>
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ __('تقييم الموظفين') }}</h4>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success" x-data x-init="setTimeout(() => $el.remove(), 3000)">
                {{ session('message') }}
            </div>
        @endif

        <!-- Search and Add New Button -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('بحث...') }}">
            </div>
            @can('إضافة معدلات اداء الموظفين')
                <div class="col-md-6">
                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal"
                        data-bs-target="#addEvaluationModal">
                        {{ __('إضافة تقييم جديد') }}
                    </button>
                </div>
            @endcan
        </div>

        <!-- Evaluations Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('الموظف') }}</th>
                                        <th>{{ __('المسمى الوظيفي') }}</th>
                                        <th>{{ __('القسم') }}</th>
                                        <th>{{ __('تاريخ التقييم') }}</th>
                                        <th>{{ __('المدير المباشر') }}</th>
                                        <th>{{ __('الدرجة الكلية') }}</th>
                                        <th>{{ __('التقدير') }}</th>
                                        {{-- @canany(['تعديل معدلات اداء الموظفين', 'حذف معدلات اداء الموظفين']) --}}
                                        <th>{{ __('الإجراءات') }}</th>
                                        {{-- @endcanany --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($evaluations as $evaluation)
                                        <tr>
                                            <td>{{ $evaluation->employee->name }}</td>
                                            <td>{{ $evaluation->employee->job?->title }}</td>
                                            <td>{{ $evaluation->employee->department?->title }}</td>
                                            <td>{{ $evaluation->evaluation_date }}</td>
                                            <td>{{ $evaluation->direct_manager }}</td>
                                            <td>{{ $evaluation->total_score }}</td>
                                            <td>{{ $evaluation->final_rating }}</td>
                                            {{-- @canany(['تعديل معدلات اداء الموظفين', 'حذف معدلات اداء الموظفين']) --}}
                                            <td>
                                                <button wire:click="view({{ $evaluation->id }})"
                                                    class="btn btn-info btn-icon-square-sm me-1" title="{{ __('عرض') }}">
                                                    <i class="las la-eye fa-lg"></i>
                                                </button>
                                                {{-- @can('تعديل معدلات أداء الموظفين') --}}
                                                <button wire:click="edit({{ $evaluation->id }})"
                                                    class="btn btn-success btn-icon-square-sm me-1" title="{{ __('تعديل') }}">
                                                    <i class="las la-edit fa-lg"></i>
                                                </button>
                                                {{-- @endcan
                                                    @can('حذف معدلات أداء الموظفين') --}}
                                                <button wire:click="confirmDelete({{ $evaluation->id }})"
                                                    class="btn btn-danger btn-icon-square-sm" title="{{ __('حذف') }}">
                                                    <i class="las la-trash fa-lg"></i>
                                                </button>
                                                {{-- @endcan --}}
                                            </td>
                                            {{-- @endcanany --}}
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <div class="alert alert-info py-3 mb-0"
                                                    style="font-size: 1.2rem; font-weight: 500;">
                                                    <i class="las la-info-circle me-2"></i>
                                                    لا توجد بيانات
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $evaluations->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Evaluation Modal -->
        <div class="modal fade" id="addEvaluationModal" tabindex="-1" wire:ignore.self data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('تقييم موظف') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="{{ $showEditModal ? 'update' : 'save' }}">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('الموظف') }}</label>
                                    <select wire:model.live="employee_id" wire:change="loadEmployeeDetails"
                                        class="form-control">
                                        <option value="">{{ __('اختر الموظف') }}</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('تاريخ التقييم') }}</label>
                                    <input type="date" wire:model="evaluation_date" class="form-control">
                                    @error('evaluation_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('المسمى الوظيفي') }}</label>
                                    <input type="text" class="form-control" value="{{ $job_title }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('القسم') }}</label>
                                    <input type="text" class="form-control" value="{{ $department }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">{{ __('المدير المباشر') }}</label>
                                    <input type="text" wire:model="direct_manager" class="form-control">
                                    @error('direct_manager')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('فترة التقييم من') }}</label>
                                    <input type="date" wire:model="evaluation_period_from" class="form-control">
                                    @error('evaluation_period_from')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('فترة التقييم إلى') }}</label>
                                    <input type="date" wire:model="evaluation_period_to" class="form-control">
                                    @error('evaluation_period_to')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('المعيار') }}</th>
                                            <th>{{ __('الوصف') }}</th>
                                            <th>{{ __('التقييم') }} (1-5)</th>
                                            <th>{{ __('ملاحظات') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($kpis as $index => $kpi)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $kpi->name }}</td>
                                                <td>{{ $kpi->description }}</td>
                                                <td>
                                                    <input type="number" wire:model="scores.{{ $kpi->id }}"
                                                        wire:change="calculateTotalScore" class="form-control"
                                                        min="1" max="5">
                                                    @error('scores.' . $kpi->id)
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        wire:model="notes.{{ $kpi->id }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end">
                                                <strong>{{ __('المجموع الكلي') }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $total_score }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $final_rating }}</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">{{ __('إغلاق') }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('حفظ') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" wire:ignore.self data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('تأكيد الحذف') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{ __('هل أنت متأكد من حذف هذا التقييم؟') }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="cancelDelete">{{ __('إلغاء') }}</button>
                        <button type="button" class="btn btn-danger"
                            wire:click="delete">{{ __('حذف') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Evaluation Modal -->
        <div class="modal fade" id="viewModal" tabindex="-1" wire:ignore.self data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('عرض تفاصيل التقييم') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($viewEvaluation)
                            <!-- Employee Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">{{ __('معلومات الموظف') }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('الاسم') }}:</label>
                                    <p class="form-control-plaintext">{{ $viewEvaluation->employee->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('المسمى الوظيفي') }}:</label>
                                    <p class="form-control-plaintext">{{ $viewEvaluation->employee->job?->title ?? __('غير محدد') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('القسم') }}:</label>
                                    <p class="form-control-plaintext">{{ $viewEvaluation->employee->department?->title ?? __('غير محدد') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('المدير المباشر') }}:</label>
                                    <p class="form-control-plaintext">{{ $viewEvaluation->direct_manager ?? __('غير محدد') }}</p>
                                </div>
                            </div>

                            <!-- Evaluation Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">{{ __('معلومات التقييم') }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('تاريخ التقييم') }}:</label>
                                    <p class="form-control-plaintext">{{ $viewEvaluation->evaluation_date->format('Y-m-d') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('فترة التقييم') }}:</label>
                                    <p class="form-control-plaintext">
                                        {{ $viewEvaluation->evaluation_period_from->format('Y-m-d') }} - {{ $viewEvaluation->evaluation_period_to->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>

                            <!-- KPI Scores -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">{{ __('نتائج التقييم') }}</h6>
                                </div>
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ __('المعيار') }}</th>
                                                    <th>{{ __('الوصف') }}</th>
                                                    <th>{{ __('الدرجة') }}</th>
                                                    <th>{{ __('ملاحظات') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($viewEvaluation->kpis as $index => $kpi)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $kpi->name }}</td>
                                                        <td>{{ $kpi->description }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary fs-6">{{ $kpi->pivot->score }}</span>
                                                        </td>
                                                        <td>{{ $kpi->pivot->notes ?? __('لا توجد ملاحظات') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-success">
                                                    <td colspan="3" class="text-end">
                                                        <strong>{{ __('المجموع الكلي') }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong class="fs-5">{{ $viewEvaluation->total_score }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong class="fs-5">{{ $viewEvaluation->final_rating }}</strong>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeView">{{ __('إغلاق') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const addEvaluationModal = new bootstrap.Modal(document.getElementById('addEvaluationModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

            window.addEventListener('show-evaluation-modal', event => {
                addEvaluationModal.show();
            });

            window.addEventListener('hide-evaluation-modal', event => {
                addEvaluationModal.hide();
            });

            window.addEventListener('show-delete-modal', event => {
                deleteModal.show();
            });

            window.addEventListener('hide-delete-modal', event => {
                deleteModal.hide();
            });

            window.addEventListener('show-view-modal', event => {
                viewModal.show();
            });

            window.addEventListener('hide-view-modal', event => {
                viewModal.hide();
            });
        });
    </script>
@endscript

<?php

use Livewire\Volt\Component;
use App\Models\Project;
use Livewire\WithPagination;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    public $budgetFilter = 'all';

    public function getProjectsProperty()
    {
        $query = Project::with(['createdBy', 'updatedBy'])->latest();
        
        if ($this->budgetFilter !== 'all') {
            $query->whereHas('operations', function($q) {
                // Filter will be applied after loading
            });
        }
        
        return $query->paginate(10);
    }

    public function getBudgetStatus($project)
    {
        $totalPaid = $project->operations()->where('pro_type', 2)->sum('pro_value');
        $totalReceived = $project->operations()->where('pro_type', 1)->sum('pro_value');
        $budget = $totalReceived;
        $spent = $totalPaid;
        
        if ($budget == 0) return ['status' => 'no_budget', 'class' => 'secondary', 'text' => 'لا توجد ميزانية'];
        
        $percentage = ($spent / $budget) * 100;
        
        if ($spent > $budget) {
            return ['status' => 'over', 'class' => 'danger', 'text' => 'تجاوزت الميزانية'];
        } elseif ($spent == $budget) {
            return ['status' => 'equal', 'class' => 'warning', 'text' => 'مساوية للميزانية'];
        } else {
            return ['status' => 'under', 'class' => 'success', 'text' => 'أقل من الميزانية'];
        }
    }

    public function delete(Project $project)
    {
        $project->delete();
        session()->flash('success', 'تم حذف المشروع بنجاح');
    }

    public function getStatusBadgeClass($status)
    {
        return match ($status) {
            'pending' => 'bg-warning',
            'in_progress' => 'bg-info',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusText($status)
    {
        return match ($status) {
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => 'غير معروف',
        };
    }
}; ?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">المشاريع</h2>
        <a href="{{ route('projects.create') }}" class="btn btn-main">
            <i class="las la-plus"></i> إضافة مشروع جديد
        </a>
    </div>
    
    <div class="mb-3">
        <label class="form-label">فلتر حالة الميزانية:</label>
        <select wire:model.live="budgetFilter" class="form-select" style="max-width: 300px;">
            <option value="all">الكل</option>
            <option value="over">تجاوزت الميزانية</option>
            <option value="equal">مساوية للميزانية</option>
            <option value="under">أقل من الميزانية</option>
        </select>
    </div>
    <br>
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                x-on:click="show = false"></button>
        </div>
    @endif

    <div class="kanban-board d-flex flex-row overflow-auto gap-4" style="min-height: 60vh;">
        @php
            $statuses = [
                'pending' => 'قيد الانتظار',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتمل',
                'cancelled' => 'ملغي',
            ];
        @endphp
        @foreach ($statuses as $statusKey => $statusLabel)
            <div class="kanban-column card flex-shrink-0" style="min-width: 320px; max-width: 350px;">
                <div class="card-header text-center fw-bold bg-light">{{ $statusLabel }}</div>
                <div class="card-body p-2" style="min-height: 50vh;">
                    @php
                        $projectsForStatus = $this->projects->filter(fn($p) => $p->status === $statusKey);
                    @endphp
                    @forelse($projectsForStatus as $project)
                        @php
                            $budgetStatus = $this->getBudgetStatus($project);
                            $shouldShow = $this->budgetFilter === 'all' || $budgetStatus['status'] === $this->budgetFilter;
                        @endphp
                        @if($shouldShow)
                        <div
                            class="kanban-card card mb-3 shadow-sm border-{{ $this->getStatusBadgeClass($project->status) }}">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold">{{ $project->name }}</span>
                                    <span class="badge {{ $this->getStatusBadgeClass($project->status) }}">
                                        {{ $this->getStatusText($project->status) }}
                                    </span>
                                </div>
                                <div class="text-muted mb-1" style="font-size: 0.95rem;">
                                    {{ Str::limit($project->description, 50) }}
                                </div>
                                <div class="mb-1" style="font-size: 0.9rem;">
                                    <span>تاريخ البدء:
                                        {{ $project->start_date?->format('Y-m-d') ?? 'غير محدد' }}</span><br>
                                    <span>تاريخ الانتهاء المتوقع:
                                        {{ $project->end_date?->format('Y-m-d') ?? 'غير محدد' }}</span><br>
                                    <span>تاريخ الانتهاء الفعلي:
                                        {{ $project->actual_end_date?->format('Y-m-d') ?? 'غير محدد' }}</span>
                                </div>

                                <div class="mb-1" style="font-size: 0.9rem;">
                                    <span>أنشئ بواسطة: {{ $project->createdBy?->name ?? '-' }}</span><br>
                                    <span>تم التحديث بواسطة: {{ $project->updatedBy?->name ?? '-' }}</span>
                                </div>
                                
                                @php
                                    $budgetStatus = $this->getBudgetStatus($project);
                                    $shouldShow = $this->budgetFilter === 'all' || $budgetStatus['status'] === $this->budgetFilter;
                                @endphp
                                
                                @if($shouldShow)
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $budgetStatus['class'] }}">
                                            <i class="las la-wallet"></i> {{ $budgetStatus['text'] }}
                                        </span>
                                    </div>
                                @endif
                               
                                    <div class="d-flex gap-2 mt-2">
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-primary btn-sm">
                                                <i class="las la-eye fa-lg"></i>
                                            </a>
                                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-success btn-sm">
                                                <i class="las la-edit fa-lg"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                wire:click="delete({{ $project->id }})"
                                                onclick="confirm('هل أنت متأكد من حذف هذا المشروع؟') || event.stopImmediatePropagation()">
                                                <i class="las la-trash fa-lg"></i>
                                            </button>
                                    </div>
                               </div>
                        </div>
                        @endif
                    @empty
                        <div class="alert alert-info py-2 mb-0 text-center" style="font-size: 1rem; font-weight: 500;">
                            <i class="las la-info-circle me-2"></i>
                            لا توجد مشاريع في هذه الحالة
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

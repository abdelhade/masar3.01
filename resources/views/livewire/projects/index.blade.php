<?php

use Livewire\Volt\Component;
use App\Models\Project;
use Livewire\WithPagination;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    public function getProjectsProperty()
    {
        return Project::with(['createdBy', 'updatedBy'])
            ->latest()
            ->paginate(10);
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
                                @canany(abilities: ['تعديل المشاريع', 'حذف المشاريع'])
                                    <div class="d-flex gap-2 mt-2">
                                        @can('تعديل المشاريع')
                                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-success btn-sm">
                                                <i class="las la-edit fa-lg"></i>
                                            </a>
                                        @endcan
                                        @can('حذف المشاريع')
                                            <button type="button" class="btn btn-danger btn-sm"
                                                wire:click="delete({{ $project->id }})"
                                                onclick="confirm('هل أنت متأكد من حذف هذا المشروع؟') || event.stopImmediatePropagation()">
                                                <i class="las la-trash fa-lg"></i>
                                            </button>
                                        @endcan
                                    </div>
                                @endcanany
                            </div>
                        </div>
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

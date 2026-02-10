@extends('progress::layouts.app')

@section('title', __('general.daily_progress_list'))

@push('styles')
<style>
    
    .select2-container--default .select2-selection--single {
        height: calc(2.5rem + 2px) !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0.375rem 0.75rem !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 0 !important;
        line-height: normal !important;
        color: #495057 !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        right: 10px !important;
    }
    
    [dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__arrow {
        right: auto !important;
        left: 10px !important;
    }
    
    .select2-container {
        width: 100% !important;
    }
    
    
    .project-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 25px;
        overflow: hidden;
    }
    
    .project-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .project-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 25px;
        cursor: pointer;
        position: relative;
    }
    
    .project-card-header:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
    
    .project-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .project-stats {
        display: flex;
        gap: 20px;
        margin-top: 12px;
        flex-wrap: wrap;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.15);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    
    .stat-item i {
        font-size: 0.85rem;
    }
    
    .project-card-body {
        padding: 0;
        background: #f8f9fa;
    }
    
    .progress-item {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 18px 25px;
        transition: all 0.2s ease;
    }
    
    .progress-item:hover {
        background: #f8f9fa;
        border-left: 4px solid #667eea;
        padding-left: 21px;
    }
    
    .progress-item:last-child {
        border-bottom: none;
    }
    
    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    
    .item-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: #2d3748;
        margin-bottom: 6px;
    }
    
    .item-meta {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    
    .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        background: #e9ecef;
        color: #495057;
    }
    
    .meta-badge i {
        font-size: 0.75rem;
    }
    
    .quantity-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 1.1rem;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }
    
    .completion-badge {
        background: linear-gradient(135deg,rgb(0, 47, 255) 0%,rgb(0, 64, 107) 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(245, 87, 108, 0.3);
        cursor: help;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .completion-badge small {
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .completion-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
    }
    
    .item-actions {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    
    .item-actions > div {
        display: flex;
        flex-direction: column;
    }
    
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    
    .collapse-icon.collapsed {
        transform: rotate(-90deg);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .search-highlight {
        background: #fff3cd;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    
    .dark-mode .project-card {
        background: #1e2125;
    }
    
    .dark-mode .project-card-body {
        background: #2d3238;
    }
    
    .dark-mode .progress-item {
        background: #1e2125;
        border-bottom-color: #495057;
    }
    
    .dark-mode .progress-item:hover {
        background: #2d3238;
    }
    
    .dark-mode .item-title {
        color: #dee2e6;
    }
    
    .dark-mode .meta-badge {
        background: #495057;
        color: #dee2e6;
    }
    
    
    @media (max-width: 768px) {
        .project-stats {
            gap: 10px;
        }
        
        .stat-item {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        
        .item-header {
            flex-direction: column;
            gap: 12px;
        }
        
        .item-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
    
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .dark-mode .filter-card {
        background: #1e2125;
    }
    
    
    .subproject-section {
        background: #f0f0f0 !important;
        border-bottom: 2px solid #667eea !important;
        padding: 15px 25px !important;
        margin-top: 0 !important;
        transition: all 0.2s ease;
    }
    
    .subproject-section:hover {
        background: #e8e8e8 !important;
    }
    
    .dark-mode .subproject-section {
        background: #2d3238 !important;
        border-bottom-color: #667eea !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-calendar-check text-primary me-2"></i>
                {{ __('general.daily_progress_list') }}
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                @if(request()->boolean('view_all'))
                    عرض جميع السجلات
                @elseif(request()->filled('from_date') || request()->filled('to_date'))
                    عرض السجلات المفلترة
                @else
                    عرض سجلات آخر 30 يوم (استخدم "عرض الكل" لرؤية جميع السجلات)
                @endif
            </p>
        </div>
        <a href="{{ route('progress.daily-progress.create') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i> {{ __('general.add_progress') }}
        </a>
    </div>

    
    <div class="filter-card">
        <form method="GET" action="{{ route('progress.daily-progress.index') }}" class="row g-3">
            <input type="hidden" name="view_all" value="{{ request('view_all') }}">
            
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-project-diagram me-1"></i>
                    {{ __('general.project') }}
                </label>
                <select name="project_id" id="project_filter" class="form-select">
                    <option value="">{{ __('general.all_projects') }}</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ __('general.from_date') }}
                </label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar-check me-1"></i>
                    {{ __('general.to_date') }}
                </label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
            </div>
            
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> {{ __('general.filter') }}
                </button>
                <a href="{{ route('progress.daily-progress.index', ['view_all' => 1]) }}" class="btn btn-secondary">
                    <i class="fas fa-list me-1"></i> {{ __('general.view_all') }}
                </a>
                <a href="{{ route('progress.daily-progress.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo me-1"></i> إعادة تعيين
                </a>
            </div>
        </form>

        
        <div class="mt-3">
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" id="global_search" class="form-control" 
                       placeholder="ابحث في جميع المشاريع والبنود (اسم المشروع، البند، الموظف، الملاحظات...)">
                <button type="button" id="clear_search" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <small class="text-muted mt-1 d-block" id="search_counter" style="display:none;"></small>
        </div>
    </div>

    
    <div id="projects-container">
        @forelse($groupedProgress as $projectId => $data)
            <div class="project-card" data-project-id="{{ $projectId }}">
                
                <div class="project-card-header" data-bs-toggle="collapse" data-bs-target="#project-{{ $projectId }}">
                    <div class="project-card-title">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-project-diagram"></i>
                            <span class="project-name">{{ $data['project']->name ?? 'مشروع بدون اسم' }}</span>
                        </div>
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </div>
                    <div class="project-stats">
                        <div class="stat-item">
                            <i class="fas fa-list-ol"></i>
                            <span>{{ $data['records_count'] }} سجل</span>
                        </div>
                      
                        <div class="stat-item">
                            <i class="fas fa-percentage"></i>
                            <span>متوسط الإنجاز: {{ $data['avg_completion'] }}%</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <span>آخر تحديث: {{ \Carbon\Carbon::parse($data['latest_date'])->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                
                <div class="collapse show project-card-body" id="project-{{ $projectId }}">
                    @foreach($data['subprojects'] as $subprojectName => $subprojectData)
                        
                        @if($subprojectData['subproject'] ?? null)
                            <div class="subproject-section">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-layer-group text-primary"></i>
                                        <h6 class="mb-0 fw-bold" style="color: #667eea;">
                                            {{ $subprojectData['display_name'] }}
                                        </h6>
                                        <span class="badge bg-primary ms-2">
                                            {{ $subprojectData['records_count'] }} سجل
                                        </span>
                                    </div>
                                    <div class="d-flex gap-3">
                                     
                                        <small class="text-muted">
                                            <i class="fas fa-percentage me-1"></i>
                                            متوسط: {{ $subprojectData['avg_completion'] }}%
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="subproject-section" style="background: #f9f9f9; border-bottom: 2px solid #dee2e6;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-layer-group text-secondary"></i>
                                        <h6 class="mb-0 fw-bold text-muted">
                                            {{ $subprojectData['display_name'] ?? 'بدون مشروع فرعي' }}
                                        </h6>
                                        <span class="badge bg-secondary ms-2">
                                            {{ $subprojectData['records_count'] }} سجل
                                        </span>
                                    </div>
                                    <div class="d-flex gap-3">
                                   
                                        @if($subprojectData['avg_completion'] ?? null)
                                        <small class="text-muted">
                                            <i class="fas fa-percentage me-1"></i>
                                            متوسط: {{ $subprojectData['avg_completion'] }}%
                                        </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        
                        @foreach($subprojectData['records'] as $progress)
                        <div class="progress-item" data-searchable="{{ strtolower($progress->projectItem->workItem->name ?? '') }} {{ strtolower($progress->projectItem->subproject->name ?? '') }} {{ strtolower($progress->employee->name ?? '') }} {{ strtolower($progress->notes ?? '') }}">
                            <div class="item-header">
                                <div class="flex-grow-1">
                                    <div class="item-title">
                                        <i class="fas fa-tasks text-primary me-2"></i>
                                        {{ $progress->projectItem->workItem->name ?? '-' }}
                                    </div>
                                    
                                    <div class="item-meta">
                                        @if($progress->projectItem->subproject ?? null)
                                            <span class="meta-badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                                <i class="fas fa-layer-group"></i>
                                                {{ $progress->projectItem->subproject->name }}
                                            </span>
                                        @endif
                                        
                                        @if($progress->projectItem->workItem->category ?? null)
                                            <span class="meta-badge">
                                                <i class="fas fa-folder"></i>
                                                {{ $progress->projectItem->workItem->category->name }}
                                            </span>
                                        @endif
                                        
                                        @if($progress->projectItem->workItem->unit ?? null)
                                            <span class="meta-badge">
                                                <i class="fas fa-ruler"></i>
                                                {{ $progress->projectItem->workItem->unit }}
                                            </span>
                                        @endif
                                        
                                        @if($progress->projectItem->is_measurable ?? false)
                                            <span class="meta-badge" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;" title="قابل للقياس">
                                                <i class="fas fa-check-circle"></i>
                                                قابل للقياس
                                            </span>
                                        @else
                                            <span class="meta-badge" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white;" title="غير قابل للقياس">
                                                <i class="fas fa-times-circle"></i>
                                                غير قابل للقياس
                                            </span>
                                        @endif
                                        
                                        <span class="meta-badge">
                                            <i class="fas fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($progress->progress_date)->format('d-m-Y') }}
                                        </span>
                                        
                                        @if($progress->employee ?? null)
                                            <span class="meta-badge">
                                                <i class="fas fa-user"></i>
                                                {{ $progress->employee->name }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($progress->notes)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-sticky-note me-1"></i>
                                                {{ $progress->notes }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="item-actions">
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="quantity-badge" title="الكمية المُنفَّذة في هذا السجل">
                                                <i class="fas fa-box me-1"></i>
                                                {{ number_format($progress->quantity, 2) }}
                                            </span>
                                            
                                            @php
                                                $itemTotalQty = $progress->projectItem->total_quantity ?? 1;
                                                $recordPercentage = $itemTotalQty > 0 ? round(($progress->quantity / $itemTotalQty) * 100, 2) : 0;
                                                $cumulativePercentage = $progress->completion_percentage;
                                            @endphp
                                            
                                            <span class="completion-badge" 
                                                  title="نسبة هذا السجل: {{ $recordPercentage }}% | الإجمالي التراكمي: {{ $cumulativePercentage }}%"
                                                  data-bs-toggle="tooltip"
                                                  data-bs-html="true">
                                                <i class="fas fa-percentage me-1"></i>
                                                {{ $recordPercentage }}%
                                                <small class="ms-1" style="opacity: 0.8;">
                                                    (إجمالي: {{ $cumulativePercentage }}%)
                                                </small>
                                            </span>
                                        </div>
                                        
                                        @php
                                            $item = $progress->projectItem;
                                            // المتبقي من المشروع ككل (الباقي لإكمال البند بالكامل)
                                            $totalCompleted = $item->dailyProgresses->sum('quantity');
                                            $remainingTotal = $item->total_quantity - $totalCompleted;
                                            
                                            // المتبقي من اليوم (إذا كان هناك daily_quantity مخطط)
                                            $todayQuantity = $item->dailyProgresses->where('progress_date', $progress->progress_date)->sum('quantity');
                                            $remainingToday = $item->daily_quantity ? max(0, $item->daily_quantity - $todayQuantity) : null;
                                        @endphp
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            المتبقي الكلي: {{ number_format($remainingTotal, 2) }} 
                                            من {{ number_format($item->total_quantity, 2) }}
                                            @if($remainingToday !== null)
                                                | المتبقي اليوم: {{ number_format($remainingToday, 2) }}
                                                من {{ number_format($item->daily_quantity, 2) }}
                                            @endif
                                        </small>
                                    </div>
                                    
                                    @canany(['dailyprogress-edit', 'dailyprogress-delete'])
                                        <div class="btn-group">
                                            @can('edit daily-progress')
                                                <a href="{{ route('progress.daily-progress.edit', $progress) }}" 
                                                   class="btn btn-sm btn-success" 
                                                   title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            
                                            @can('delete daily-progress')
                                                <form action="{{ route('progress.daily-progress.destroy', $progress) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="return confirm('{{ __('general.confirm_delete') }}')" 
                                                            type="submit" 
                                                            class="btn btn-sm btn-danger"
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    @endcanany
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                    @endforeach
                    
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h4>لا توجد سجلات تقدم يومي</h4>
                <p class="text-muted">لم يتم العثور على أي سجلات تقدم يومي. ابدأ بإضافة سجل جديد.</p>
                <a href="{{ route('progress.daily-progress.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i> إضافة سجل جديد
                </a>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // تهيئة Select2
    const isArabic = '{{ session('locale', app()->getLocale()) }}' === 'ar';
    const noResultsText = isArabic ? 'لا توجد نتائج' : 'No results found';
    
    $('#project_filter').select2({
        placeholder: '{{ __('general.all_projects') }}',
        allowClear: true,
        width: '100%',
        dir: isArabic ? 'rtl' : 'ltr',
        language: {
            noResults: function() {
                return noResultsText;
            }
        }
    });
    
    // تفعيل Bootstrap Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // تدوير أيقونة السهم عند الطي والفتح
    $('.project-card-header').on('click', function() {
        $(this).find('.collapse-icon').toggleClass('collapsed');
    });
    
    // البحث الشامل
    $('#global_search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        let visibleProjectsCount = 0;
        let visibleItemsCount = 0;
        
        if (searchTerm === '') {
            // إظهار كل شيء
            $('.project-card').show();
            $('.progress-item').show();
            $('#search_counter').hide();
            return;
        }
        
        $('.project-card').each(function() {
            const projectCard = $(this);
            const projectName = projectCard.find('.project-name').text().toLowerCase();
            let hasVisibleItems = false;
            
            // البحث في البنود
            projectCard.find('.progress-item').each(function() {
                const item = $(this);
                const searchableText = item.data('searchable') || '';
                
                if (projectName.includes(searchTerm) || searchableText.includes(searchTerm)) {
                    item.show();
                    hasVisibleItems = true;
                    visibleItemsCount++;
                } else {
                    item.hide();
                }
            });
            
            // إظهار/إخفاء المشروع بناءً على وجود بنود مطابقة
            if (hasVisibleItems) {
                projectCard.show();
                projectCard.find('.collapse').addClass('show');
                visibleProjectsCount++;
            } else {
                projectCard.hide();
            }
        });
        
        // عرض نتائج البحث
        const resultText = '<i class="fas fa-search me-1"></i>' +
            'تم العثور على <strong>' + visibleItemsCount + '</strong> سجل في <strong>' + visibleProjectsCount + '</strong> مشروع';
        $('#search_counter').show().html(resultText);
    });
    
    // مسح البحث
    $('#clear_search').on('click', function() {
        $('#global_search').val('');
        $('.project-card').show();
        $('.progress-item').show();
        $('#search_counter').hide();
        $('#global_search').focus();
    });
});
</script>
@endpush

@endsection

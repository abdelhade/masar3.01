@extends('progress::layouts.app')

@section('title', __('general.daily_progress_title'))

@push('styles')
<style>
    
    .quantity-input {
        transition: border-color 0.3s ease;
    }
    
    .quantity-input.border-success {
        border-color: #28a745 !important;
        border-width: 2px !important;
    }
    
    .quantity-input.border-warning {
        border-color: #ffc107 !important;
        border-width: 2px !important;
    }
    
    .quantity-input.border-danger {
        border-color: #dc3545 !important;
        border-width: 2px !important;
    }
    
    .comparison-text {
        display: block !important;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 6px;
        margin-top: 8px;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .text-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .text-warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .text-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .text-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }
    
    .warning-text {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 6px 10px;
        border-radius: 4px;
        margin-top: 5px;
        background-color: #fff3cd;
        color: #856404;
        border-left: 3px solid #ffc107;
    }
    
    
    .dark-mode .text-success {
        background-color: rgba(40, 167, 69, 0.2);
        color: #5cb85c;
    }
    
    .dark-mode .text-warning {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }
    
    .dark-mode .text-danger {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }
    
    .dark-mode .text-info {
        background-color: rgba(23, 162, 184, 0.2);
        color: #17a2b8;
    }
    
    .dark-mode .warning-text {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }
    
    
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
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0.375rem 0.75rem !important;
        outline: none !important;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #2c7be5 !important;
        box-shadow: 0 0 0 0.2rem rgba(44, 123, 229, 0.25) !important;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #2c7be5 !important;
        color: white !important;
    }
    
    .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    
    .dark-mode .select2-container--default .select2-selection--single {
        background-color: #1e2125 !important;
        border-color: #495057 !important;
    }
    
    .dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #dee2e6 !important;
    }
    
    .dark-mode .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d !important;
    }
    
    .dark-mode .select2-dropdown {
        background-color: #1e2125 !important;
        border-color: #495057 !important;
    }
    
    .dark-mode .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: #2d3238 !important;
        border-color: #495057 !important;
        color: #dee2e6 !important;
    }
    
    .dark-mode .select2-container--default .select2-results__option {
        background-color: #1e2125 !important;
        color: #dee2e6 !important;
    }
    
    .dark-mode .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #2c7be5 !important;
        color: white !important;
    }
    
    .dark-mode .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #2d3238 !important;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="main-card card shadow-lg border-0">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%); border-radius: 0.75rem 0.75rem 0 0;">
            <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> {{ __('general.daily_progress_title') }}</h5>
            @can('dailyprogress-list')
            <a href="{{ route('progress.daily-progress.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> {{ __('general.back_to_list') }}
            </a>
            @endcan
        </div>

        <div class="card-body bg-light">
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {!! session('warning') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('progress.daily-progress.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-project-diagram me-2"></i>{{ __('general.project') }}
                        </label>
                        <select name="project_id" id="project_id" class="form-select shadow-sm" required>
                            <option value="">{{ __('general.select_project') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-layer-group me-2"></i>المشروع الفرعي
                        </label>
                        <select name="subproject_id" id="subproject_id" class="form-select shadow-sm">
                            <option value="">جميع المشاريع الفرعية</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">{{ __('general.date') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-day"></i></span>
                            <input type="date" name="progress_date" class="form-control shadow-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4" id="items_table_wrapper" style="display:none;">
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" id="items_search" class="form-control" placeholder="ابحث في البنود (الاسم، الوحدة، الفئة، المشروع الفرعي، الملاحظات)...">
                                <button type="button" id="clear_search" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block" id="search_counter" style="display:none;"></small>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary active" id="view_flat">
                                    <i class="fas fa-list me-1"></i> عرض عادي
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="view_grouped">
                                    <i class="fas fa-layer-group me-1"></i> حسب المشروع الفرعي
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle border shadow-sm">
                            <thead class="table-primary">
                                <tr>
                                    <th>{{ __('general.work_item') }}</th>
                                    <th width="180">المشروع الفرعي</th>
                                    <th width="200">{{ __('general.enter_quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody id="items_table_body">
                                
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __('general.notes') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-sticky-note"></i></span>
                        <textarea name="notes" class="form-control shadow-sm" rows="3"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> {{ __('general.save_progress') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // الترجمات والإعدادات
        const translations = {
            expectedDaily: '{{ __("general.expected_daily") }}',
            day: '{{ __("general.day") }}'
        };
        
        const isArabic = '{{ session("locale", app()->getLocale()) }}' === 'ar';
        const noResultsText = isArabic ? 'لا توجد نتائج' : 'No results found';
        const searchingText = isArabic ? 'جاري البحث...' : 'Searching...';
        
        let currentViewMode = 'flat'; // flat or grouped
        let allItemsData = []; // Store all items data
        
        // Setup group toggle functionality (only once, using event delegation)
        function setupGroupToggle() {
            // Remove any existing listeners first to avoid duplicates
            $(document).off('click', '.group-header');
            
            // Add listener with event delegation (works for dynamically added elements)
            $(document).on('click', '.group-header', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $header = $(this);
                
                // Find all items that come after this header until the next header
                let $groupItems = $();
                let $nextElement = $header.next();
                
                while ($nextElement.length && !$nextElement.hasClass('group-header')) {
                    if ($nextElement.hasClass('group-item')) {
                        $groupItems = $groupItems.add($nextElement);
                    }
                    $nextElement = $nextElement.next();
                }
                
                const $toggle = $header.find('.group-toggle');
                
                if ($groupItems.length === 0) {
                    console.warn('⚠️ No items found for this group');
                    return;
                }
                
                // Check if items are currently visible (check first item)
                const isVisible = $groupItems.first().is(':visible');
                
                // Toggle visibility with smooth animation
                if (isVisible) {
                    // Currently open, so close it
                    $groupItems.slideUp(300);
                    $toggle.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                } else {
                    // Currently closed, so open it
                    $groupItems.slideDown(300);
                    $toggle.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }
            });
        }
        
        // Initialize group toggle on page load
        setupGroupToggle();
        
        // تهيئة Select2 للمشاريع مع البحث
        $('#project_id').select2({
            placeholder: '{{ __("general.select_project") }}',
            allowClear: true,
            width: '100%',
            dir: isArabic ? 'rtl' : 'ltr',
            language: {
                noResults: function() {
                    return noResultsText;
                },
                searching: function() {
                    return searchingText;
                }
            },
            minimumResultsForSearch: 0
        });
        
        // تهيئة Select2 للمشاريع الفرعية
        $('#subproject_id').select2({
            placeholder: 'جميع المشاريع الفرعية',
            allowClear: true,
            width: '100%',
            dir: isArabic ? 'rtl' : 'ltr',
            language: {
                noResults: function() {
                    return noResultsText;
                },
                searching: function() {
                    return searchingText;
                }
            },
            minimumResultsForSearch: 0
        });
        
        // View mode toggle
        $('#view_flat').on('click', function() {
            if (currentViewMode !== 'flat') {
                currentViewMode = 'flat';
                $('#view_flat').addClass('active');
                $('#view_grouped').removeClass('active');
                renderItems(allItemsData, 'flat');
            }
        });
        
        $('#view_grouped').on('click', function() {
            if (currentViewMode !== 'grouped') {
                currentViewMode = 'grouped';
                $('#view_grouped').addClass('active');
                $('#view_flat').removeClass('active');
                renderItems(allItemsData, 'grouped');
            }
        });
        
        // عند اختيار المشروع
        $('#project_id').on('change', function() {
            var projectId = $(this).val();
            
            // مسح البحث عند تغيير المشروع
            $('#items_search').val('');
            $('#search_counter').hide();
            
            // تحميل المشاريع الفرعية
            if (projectId) {
                // تحميل المشاريع الفرعية
                $.get('/progress/api/projects/' + projectId + '/subprojects', function(response) {
                    $('#subproject_id').empty().append('<option value="">جميع المشاريع الفرعية</option>');
                    
                    // Handle both response formats: direct array or {success: true, subprojects: [...]}
                    const subprojects = response.subprojects || response || [];
                    
                    if (Array.isArray(subprojects) && subprojects.length > 0) {
                        $.each(subprojects, function(index, subproject) {
                            const subprojectId = subproject.id || null;
                            const subprojectName = subproject.name || 'بدون اسم';
                            
                            $('#subproject_id').append('<option value="' + (subprojectId || '') + '">' + subprojectName + '</option>');
                        });
                    }
                    
                    $('#subproject_id').trigger('change');
                }).fail(function(xhr, status, error) {
                    console.error('Error loading subprojects:', error);
                    $('#subproject_id').empty().append('<option value="">جميع المشاريع الفرعية</option>');
                });
                
                // تحميل البنود
                $.get('/progress/api/project-items/' + projectId, function(data) {
                    allItemsData = data; // Store data
                    $('#items_table_body').empty();
                    if (data.length > 0) {
                        $('#items_table_wrapper').show();
                        renderItems(data, currentViewMode);
                    } else {
                        $('#items_table_wrapper').hide();
                    }
                }).fail(function() {
                    $('#items_table_wrapper').hide();
                    alert('حدث خطأ أثناء تحميل البنود. يرجى المحاولة مرة أخرى.');
                });
            } else {
                $('#items_table_wrapper').hide();
                $('#items_table_body').empty();
                $('#subproject_id').empty().append('<option value="">جميع المشاريع الفرعية</option>');
            }
        });
        
        // عند اختيار المشروع الفرعي - فلترة البنود
        $('#subproject_id').on('change', function() {
            var subprojectId = $(this).val();
            
            if (allItemsData.length > 0) {
                // فلترة البيانات حسب المشروع الفرعي
                let filteredData = allItemsData;
                
                if (subprojectId) {
                    // الحصول على اسم المشروع الفرعي المختار
                    const selectedSubprojectName = $(this).find('option:selected').text();
                    
                    // فلترة البنود حسب المشروع الفرعي
                    filteredData = allItemsData.filter(function(item) {
                        // البحث عن subproject_id في البيانات
                        // إذا كان item يحتوي على subproject_id، قارنه
                        // وإلا قارن بالاسم
                        if (item.subproject_id) {
                            return item.subproject_id == subprojectId;
                        } else if (item.subproject_name) {
                            return item.subproject_name === selectedSubprojectName;
                        }
                        return false;
                    });
                }
                
                // إعادة عرض البنود المفلترة
                renderItems(filteredData, currentViewMode);
            }
        });
        
        // Function to render items
        function renderItems(data, mode) {
            $('#items_table_body').empty();
            
            if (mode === 'grouped') {
                renderGroupedItems(data);
            } else {
                renderFlatItems(data);
            }
            
            setupItemInteractions();
            setupItemsSearch();
        }
        
        // Render flat view
        function renderFlatItems(data) {
            $.each(data, function(index, item) {
                const row = createItemRow(item);
                $('#items_table_body').append(row);
            });
        }
        
        // Render grouped view
        function renderGroupedItems(data) {
            // Group items by subproject
            const grouped = {};
            
            $.each(data, function(index, item) {
                const subprojectName = item.subproject_name || 'بدون تصنيف';
                if (!grouped[subprojectName]) {
                    grouped[subprojectName] = [];
                }
                grouped[subprojectName].push(item);
            });
            
            // Render each group
            const sortedGroups = Object.keys(grouped).sort();
            
            $.each(sortedGroups, function(index, groupName) {
                const items = grouped[groupName];
                
                // Group header
                const groupColor = groupName === 'بدون تصنيف' 
                    ? 'linear-gradient(135deg, #6c757d 0%, #495057 100%)'
                    : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                
                // Escape HTML to prevent XSS
                const escapeHtml = (text) => {
                    const map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
                };
                
                const escapedGroupName = escapeHtml(groupName);
                
                // Use original groupName in data-group attribute (not escaped) to match items
                // Escape quotes and special characters for HTML attribute
                const safeGroupName = groupName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                const groupHeader = `
                      <tr class="group-header" style="background: ${groupColor}; cursor: pointer; user-select: none;" data-group="${safeGroupName}">
                          <td colspan="3" class="fw-bold py-3">
                             <div class="d-flex align-items-center justify-content-between" style="color: #ffffff; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                                <div>
                                    <i class="fas fa-layer-group me-2" style="color:rgb(255, 255, 255);"></i>
                                    <span style="color:rgb(0, 0, 0); font-weight: 700; font-size: 1.1rem;">${escapedGroupName}</span>
                                    <span class="badge bg-light text-dark ms-2" style="font-weight: 600;">${items.length} بند</span>
                                </div>
                                <i class="fas fa-chevron-down group-toggle" style="color: #ffffff; font-size: 1.2rem;"></i>
                            </div>
                        </td>
                    </tr>
                `;
                const $groupHeaderRow = $(groupHeader);
                $('#items_table_body').append($groupHeaderRow);
                
                // Group items - store group name before creating rows
                const currentGroupName = groupName; // Store in closure
                
                $.each(items, function(i, item) {
                    const row = createItemRow(item);
                    const $row = $(row);
                    $row.addClass('group-item').attr('data-group', currentGroupName);
                    // Hide items by default (accordion starts collapsed)
                    $row.hide();
                    $('#items_table_body').append($row);
                });
            });
            
            // Group toggle functionality - setup once using event delegation
            // This will work for dynamically added elements
            setupGroupToggle();
        }
        
        // Create item row
        function createItemRow(item) {
            var totalQty = parseFloat(item.total_quantity) || 0;
            var completedQty = parseFloat(item.completed_quantity) || 0;
            var remainingQty = totalQty - completedQty;
            var todayExecuted = parseFloat(item.today_executed_quantity) || 0;

            var categoryHtml = item.work_item.category ? `<small class="text-muted d-block"><i class="fas fa-folder me-1"></i>${item.work_item.category.name}</small>` : '';
            var unitHtml = item.work_item.unit ? `<small class="text-muted d-block"><i class="fas fa-ruler me-1"></i>${item.work_item.unit}</small>` : '';
            var measurableHtml = '';
            if (item.is_measurable) {
                measurableHtml = `<small class="d-block mt-1"><span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;"><i class="fas fa-check-circle me-1"></i>قابل للقياس</span></small>`;
            } else {
                measurableHtml = `<small class="d-block mt-1"><span class="badge" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white;"><i class="fas fa-times-circle me-1"></i>غير قابل للقياس</span></small>`;
            }
            var notesHtml = item.notes ? `<small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>${item.notes.substring(0, 50)}${item.notes.length > 50 ? '...' : ''}</small>` : '';
            var estimatedDailyHtml = item.estimated_daily_qty ? `<small class="d-block mt-1"><span class="badge bg-info"><i class="fas fa-chart-line me-1"></i>${translations.expectedDaily}: ${parseFloat(item.estimated_daily_qty).toFixed(2)} ${item.work_item.unit || ''}/${translations.day}</span></small>` : '';
            
            // Today's executed quantity
            var todayExecutedHtml = '';
            if (todayExecuted > 0) {
                todayExecutedHtml = `<small class="d-block mt-1"><span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>منفذ اليوم: ${todayExecuted.toFixed(2)} ${item.work_item.unit || ''}</span></small>`;
            }
            
            // Subproject display
            var subprojectHtml = '';
            if (item.subproject_name) {
                subprojectHtml = `
                    <div class="text-center">
                        <span class="badge bg-gradient subproject-badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: black; padding: 0.5rem 1rem; font-size: 0.85rem;">
                            <i class="fas fa-layer-group me-1"></i>
                            ${item.subproject_name}
                        </span>
                    </div>
                `;
            } else {
                subprojectHtml = '<div class="text-center"><small class="text-muted">-</small></div>';
            }
            
            var row = `
                <tr data-subproject="${item.subproject_name || ''}">
                    <td>
                        <div class="fw-bold text-dark">${item.work_item.name}</div>
                        ${categoryHtml}
                        ${unitHtml}
                        ${measurableHtml}
                        ${notesHtml}
                        ${estimatedDailyHtml}
                        ${todayExecutedHtml}
                    </td>
                    <td>
                        ${subprojectHtml}
                    </td>
                    <td>
                        <input type="number"
                            name="quantities[${item.id}]"
                            class="form-control quantity-input shadow-sm"
                            step="0.01"
                            min="0"
                            value="0"
                            data-remaining="${remainingQty}"
                            data-total="${totalQty}"
                            data-estimated="${item.estimated_daily_qty || 0}"
                            data-today-executed="${todayExecuted}"
                            data-item-name="${item.work_item.name}"
                            data-unit="${item.work_item.unit}">
                        <div class="warning-text-${item.id} warning-text" style="display:none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>تحذير:</strong> تجاوزت الكمية المتبقية
                        </div>
                        <div class="comparison-text-${item.id} comparison-text" style="display:none;"></div>
                    </td>
                </tr>
            `;
            return row;
        }
        
        // Setup item interactions
        function setupItemInteractions() {
            // التحقق من الكمية المدخلة والمقارنة مع المتوقع
            $('.quantity-input').off('input').on('input', function() {
                            var inputVal = parseFloat($(this).val()) || 0;
                            var remaining = parseFloat($(this).data('remaining')) || 0;
                            var totalQty = parseFloat($(this).data('total')) || 0;
                            var estimated = parseFloat($(this).data('estimated')) || 0;
                            var unit = $(this).data('unit') || '';
                            var itemId = $(this).attr('name').match(/\d+/)[0];
                            var warningText = $('.warning-text-' + itemId);
                            var comparisonText = $('.comparison-text-' + itemId);

                            // إعادة تعيين جميع الحدود أولاً
                            $(this).removeClass('border-success border-danger border-warning');
                            
                            var hasRemainingWarning = false;

                            // إخفاء كل الرسائل أولاً
                            warningText.hide();
                            comparisonText.hide();

                            // إذا لم يتم إدخال أي قيمة، لا نعرض شيء
                            if (inputVal === 0) {
                                return;
                            }

                            var newRemaining = remaining - inputVal;

                            // تحذير الكمية المتبقية (له الأولوية الأعلى)
                            if (inputVal > remaining && remaining > 0) {
                                $(this).addClass('border-warning');
                                warningText.css('display', 'block');
                                hasRemainingWarning = true;
                            }

                            // المقارنة مع التقدم المتوقع
                            if (estimated > 0) {
                                var difference = inputVal - estimated;
                                var percentage = Math.abs((difference / estimated) * 100).toFixed(0);
                                var resultHtml = '';
                                
                                if (inputVal > estimated) {
                                    // أكبر من المتوقع (جيد)
                                    if (!hasRemainingWarning) {
                                        $(this).addClass('border-success');
                                    }
                                    comparisonText.removeClass('text-danger text-warning').addClass('text-success');
                                    resultHtml = '<i class="fas fa-arrow-up me-1"></i>' +
                                        '<strong>ممتاز!</strong> تجاوزت الكمية المتوقعة بنسبة ' + percentage + '%' +
                                        '<br><small>الكمية المُنفَّذة: ' + inputVal.toFixed(2) + ' ' + unit + 
                                        ' | المتبقي: ' + Math.max(0, newRemaining).toFixed(2) + ' من ' + totalQty.toFixed(2) + ' ' + unit + '</small>';
                                } else if (inputVal < estimated) {
                                    var achievedPercentage = ((inputVal / estimated) * 100).toFixed(0);
                                    
                                    if (achievedPercentage < 50) {
                                        // أقل بكثير من المتوقع
                                        if (!hasRemainingWarning) {
                                            $(this).addClass('border-danger');
                                        }
                                        comparisonText.removeClass('text-success text-warning').addClass('text-danger');
                                        resultHtml = '<i class="fas fa-exclamation-circle me-1"></i>' +
                                            '<strong>تحذير!</strong> الكمية أقل بكثير من المتوقع (' + achievedPercentage + '% فقط)' +
                                            '<br><small>الكمية المُنفَّذة: ' + inputVal.toFixed(2) + ' ' + unit + 
                                            ' | المتوقع: ' + estimated.toFixed(2) + ' ' + unit +
                                            ' | المتبقي: ' + Math.max(0, newRemaining).toFixed(2) + ' من ' + totalQty.toFixed(2) + ' ' + unit + '</small>';
                                    } else {
                                        // أقل قليلاً من المتوقع
                                        if (!hasRemainingWarning) {
                                            $(this).addClass('border-warning');
                                        }
                                        comparisonText.removeClass('text-success text-danger').addClass('text-warning');
                                        resultHtml = '<i class="fas fa-info-circle me-1"></i>' +
                                            '<strong>ملاحظة:</strong> الكمية أقل من المتوقع بـ ' + percentage + '%' +
                                            '<br><small>الكمية المُنفَّذة: ' + inputVal.toFixed(2) + ' ' + unit + 
                                            ' | المتوقع: ' + estimated.toFixed(2) + ' ' + unit +
                                            ' | المتبقي: ' + Math.max(0, newRemaining).toFixed(2) + ' من ' + totalQty.toFixed(2) + ' ' + unit + '</small>';
                                    }
                                } else {
                                    // مساوي للمتوقع (ممتاز)
                                    if (!hasRemainingWarning) {
                                        $(this).addClass('border-success');
                                    }
                                    comparisonText.removeClass('text-danger text-warning').addClass('text-success');
                                    resultHtml = '<i class="fas fa-check-circle me-1"></i>' +
                                        '<strong>مثالي!</strong> الكمية مطابقة تماماً للمتوقع' +
                                        '<br><small>الكمية المُنفَّذة: ' + inputVal.toFixed(2) + ' ' + unit + 
                                        ' | المتبقي: ' + Math.max(0, newRemaining).toFixed(2) + ' من ' + totalQty.toFixed(2) + ' ' + unit + '</small>';
                                }
                                
                                comparisonText.html(resultHtml).css('display', 'block');
                            } else if (inputVal > 0) {
                                // لا يوجد كمية متوقعة، نعرض فقط معلومات الكمية
                                comparisonText.removeClass('text-danger text-warning text-success').addClass('text-info');
                                var infoHtml = '<i class="fas fa-info-circle me-1"></i>' +
                                    'الكمية المُنفَّذة: ' + inputVal.toFixed(2) + ' ' + unit +
                                    ' | المتبقي: ' + Math.max(0, newRemaining).toFixed(2) + ' من ' + totalQty.toFixed(2) + ' ' + unit;
                                comparisonText.html(infoHtml).css('display', 'block');
                            }
                        });
            
            // التنقل بالـ Enter
            $('.quantity-input').off('keydown').on('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                var inputs = $('.quantity-input');
                                var idx = inputs.index(this);
                                if (idx + 1 < inputs.length) {
                                    inputs[idx + 1].focus();
                                    inputs[idx + 1].select();
                                } else {
                                    inputs[idx].blur();
                                }
                            }
                        });

            // تحديد النص عند التركيز
            $('.quantity-input').off('focus').on('focus', function() {
                $(this).select();
            });
        }
        
        // Setup search function
        function setupItemsSearch() {
            $('#items_search').off('input').on('input', function() {
                const searchTerm = $(this).val().toLowerCase().trim();
                const rows = $('#items_table_body tr');
                let visibleCount = 0;
                
                rows.each(function() {
                    const row = $(this);
                    
                    // Skip group headers
                    if (row.hasClass('group-header')) {
                        return;
                    }
                    
                    const itemName = row.find('.fw-bold').text().toLowerCase();
                    const categoryText = row.find('.fa-folder').parent().text().toLowerCase();
                    const unitText = row.find('.fa-ruler').parent().text().toLowerCase();
                    const notesText = row.find('.fa-sticky-note').parent().text().toLowerCase();
                    const subprojectText = row.find('.subproject-badge').text().toLowerCase();
                    
                    const matchesSearch = itemName.includes(searchTerm) || 
                                        categoryText.includes(searchTerm) || 
                                        unitText.includes(searchTerm) || 
                                        notesText.includes(searchTerm) ||
                                        subprojectText.includes(searchTerm);
                    
                    if (matchesSearch || searchTerm === '') {
                        row.show();
                        visibleCount++;
                    } else {
                        row.hide();
                    }
                });
                
                // تحديث عداد النتائج
                if (searchTerm) {
                    $('#search_counter').show().text(`تم العثور على ${visibleCount} بند`);
                } else {
                    $('#search_counter').hide();
                }
            });
            
            // مسح البحث
            $('#clear_search').off('click').on('click', function() {
                $('#items_search').val('');
                $('#items_table_body tr').show();
                $('#search_counter').hide();
                $('#items_search').focus();
            });
        }
    });
</script>
@endpush
@endsection

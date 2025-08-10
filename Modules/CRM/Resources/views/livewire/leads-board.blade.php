<div>
    @push('styles')
        <style>
            .leads-board {
                display: flex;
                gap: 20px;
                overflow-x: auto;
                min-height: 70vh;
                padding: 20px 0;
                scroll-behavior: smooth;
                position: relative;
            }

            .status-column {
                min-width: 300px;
                background: #f8f9fa;
                border-radius: 8px;
                padding: 15px;
                border: 2px solid transparent;
                transition: all 0.3s ease;
            }

            .status-column.dragover {
                border-color: #007bff;
                background: #e7f3ff;
                transform: scale(1.02);
                box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
            }

            .status-column.drag-active {
                border-color: #28a745;
                background: #e8f5e8;
            }

            .status-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 2px solid;
            }

            .status-title {
                font-weight: bold;
                font-size: 16px;
            }

            .leads-count {
                background: rgba(255, 255, 255, 0.8);
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 12px;
            }

            .lead-card {
                background: white;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                cursor: move;
                transition: all 0.3s ease;
                border-right: 4px solid #ddd;
                user-select: none;
            }

            .lead-card:hover {
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                transform: translateY(-2px);
            }

            .lead-card.dragging {
                opacity: 0.7;
                transform: rotate(3deg) scale(1.05);
                z-index: 1000;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            }

            .lead-card.drag-placeholder {
                opacity: 0.3;
                border: 2px dashed #007bff;
                background: #f0f8ff;
            }

            .lead-title {
                font-weight: bold;
                margin-bottom: 8px;
                color: #333;
            }

            .lead-info {
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
            }

            .lead-amount {
                font-weight: bold;
                color: #28a745;
                font-size: 14px;
            }

            .add-lead-btn {
                width: 100%;
                padding: 10px;
                border: 2px dashed #ccc;
                background: transparent;
                border-radius: 6px;
                color: #666;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .add-lead-btn:hover {
                border-color: #007bff;
                color: #007bff;
                background: rgba(0, 123, 255, 0.05);
            }

            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            }

            .modal-content {
                background: white;
                border-radius: 8px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
                color: #333;
            }

            .form-control {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }

            .form-control:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
            }

            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s ease;
            }

            .btn-primary {
                background: #007bff;
                color: white;
            }

            .btn-primary:hover {
                background: #0056b3;
            }

            .btn-secondary {
                background: #6c757d;
                color: white;
                margin-left: 10px;
            }

            .btn-danger {
                background: #dc3545;
                color: white;
                padding: 5px 10px;
                font-size: 12px;
            }

            .lead-actions {
                display: flex;
                justify-content: flex-end;
                margin-top: 10px;
            }
        </style>
    @endpush

    <div class="container-fluid" style="max-width: 1600; overflow-y: auto;">

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="leads-board d-flex flex-row flex-nowrap" id="leads-board"
            style="max-height: 80vh; overflow-x: auto; overflow-y: hidden; white-space: nowrap; scrollbar-width: thin; position: relative;">

            <!-- مؤشرات السكرول -->
            {{-- <div class="scroll-indicator left" id="scroll-left">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="scroll-indicator right" id="scroll-right">
                <i class="fas fa-chevron-right"></i>
            </div> --}}

            @foreach ($statuses as $status)
                <div class="status-column" data-status-id="{{ $status->id }}"
                    style="width: 300px; flex: 0 0 auto; border-bottom-color: {{ $status->color }};
               max-height: 76vh; display: flex; flex-direction: column; margin-right: 15px;">

                    <div class="status-header" style="border-color: {{ $status->color }}">
                        <div class="status-title" style="color: {{ $status->color }}">
                            {{ $status->name }}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="leads-count">
                                {{ isset($leads[$status->id]) ? number_format($leads[$status->id]->sum('amount')) : '0.00' }}
                                ج.م
                            </span>

                            <!-- زر التقرير -->
                            <button class="btn btn-sm btn-outline-info"
                                wire:click="openStatusReport({{ $status->id }})" title="تقرير المرحلة">
                                <i class="fas fa-chart-bar"></i>
                            </button>

                            @can('إضافة الفرص')
                                <button class="btn btn-sm btn-outline-primary"
                                    wire:click="openAddModal({{ $status->id }})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            @endcan
                        </div>
                    </div>

                    <div class="leads-container" data-status-id="{{ $status->id }}"
                        style="overflow-y: auto; flex: 1 1 0; min-height: 200px;">
                        @if (isset($leads[$status->id]))
                            @foreach ($leads[$status->id] as $lead)
                                <div class="lead-card" draggable="true" data-lead-id="{{ $lead['id'] }}"
                                    style="border-right-color: {{ $status->color }}">
                                    <div class="lead-title">{{ $lead['title'] }}</div>
                                    <div class="lead-info">
                                        <i class="fas fa-user"></i> {{ $lead['client']['name'] ?? 'غير محدد' }}
                                    </div>
                                    @if ($lead['amount'])
                                        <div class="lead-amount">
                                            <i class="fas fa-money-bill"></i> {{ number_format($lead['amount']) }}
                                            ج.م
                                        </div>
                                    @endif
                                    @if ($lead['assigned_to'])
                                        <div class="lead-info">
                                            <i class="fas fa-user-tie"></i> {{ $lead['assigned_to']['name'] }}
                                        </div>
                                    @endif
                                    <div class="lead-actions d-flex align-items-center gap-2">
                                        <button
                                            class="btn btn-success btn-sm d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;"
                                            wire:click="editLead({{ $lead['id'] }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @can('حذف الفرص')
                                            <button
                                                class="btn btn-danger btn-sm d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;"
                                                wire:click="deleteLead({{ $lead['id'] }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>

                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- نافذة إضافة فرصة جديدة --}}
        @if ($showAddModal)
            <div class="modal-overlay" wire:click.self="closeModal">
                <div class="modal-content">
                    <h4 class="mb-4">إضافة فرصة جديدة</h4>

                    <form wire:submit.prevent="addLead">
                        <div class="form-group">
                            <label class="form-label">عنوان الفرصة *</label>
                            <input type="text" class="form-control" wire:model="newLead.title" required>
                            @error('newLead.title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">العميل *</label>
                            <select class="form-control" wire:model="newLead.client_id">
                                <option value="">اختر العميل</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('newLead.client_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">القيمة المتوقعة</label>
                            <input type="number" step="0.01" class="form-control" wire:model="newLead.amount"
                                placeholder="0.00">
                            @error('newLead.amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">المصدر</label>
                            <select class="form-control" wire:model="newLead.source">
                                <option value="">اختر مصدر الفرصة</option>
                                @foreach ($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->title }}</option>
                                @endforeach
                            </select>
                            @error('newLead.source')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">مسؤول المتابعة</label>
                            <select class="form-control" wire:model="newLead.assigned_to">
                                <option value="">اختر المسؤول</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">وصف الفرصة</label>
                            <textarea class="form-control" wire:model="newLead.description" rows="3" placeholder="تفاصيل إضافية عن الفرصة"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">حفظ الفرصة</button>
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- نافذة تعديل الفرصة --}}
        @if ($showEditModal)
            <div class="modal-overlay" wire:click.self="closeModal">
                <div class="modal-content">
                    <h4 class="mb-4">تعديل الفرصة</h4>

                    <form wire:submit.prevent="updateLead">
                        <div class="form-group">
                            <label class="form-label">عنوان الفرصة *</label>
                            <input type="text" class="form-control" wire:model="editingLead.title" required>
                            @error('editingLead.title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">العميل *</label>
                            <select class="form-control" wire:model="editingLead.client_id">
                                <option value="">اختر العميل</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('editingLead.client_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">القيمة المتوقعة</label>
                            <input type="number" step="0.01" class="form-control"
                                wire:model="editingLead.amount" placeholder="0.00">
                            @error('editingLead.amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">المصدر</label>
                            <select class="form-control" wire:model="editingLead.source">
                                <option value="">اختر مصدر الفرصة</option>
                                @foreach ($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->title }}</option>
                                @endforeach
                            </select>
                            @error('editingLead.source')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">مسؤول المتابعة</label>
                            <select class="form-control" wire:model="editingLead.assigned_to">
                                <option value="">اختر المسؤول</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">وصف الفرصة</label>
                            <textarea class="form-control" wire:model="editingLead.description" rows="3"
                                placeholder="تفاصيل إضافية عن الفرصة"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">تحديث الفرصة</button>
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- نافذة تقرير المرحلة --}}
        @if ($showReportModal && $selectedStatusForReport)
            <div class="modal-overlay" wire:click.self="closeModal">
                <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">تقرير مرحلة: {{ $selectedStatusForReport->name }}</h4>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    {{-- إحصائيات سريعة --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center border-primary">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">{{ $reportData['total_leads'] }}</h5>
                                    <p class="card-text">إجمالي الفرص</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-success">
                                <div class="card-body">
                                    <h5 class="card-title text-success">
                                        {{ number_format($reportData['total_amount']) }} ج.م</h5>
                                    <p class="card-text">إجمالي القيمة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-info">
                                <div class="card-body">
                                    <h5 class="card-title text-info">{{ number_format($reportData['avg_amount']) }}
                                        ج.م</h5>
                                    <p class="card-text">متوسط القيمة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-warning">
                                <div class="card-body">
                                    <h5 class="card-title text-warning">{{ count($reportData['leads_by_source']) }}
                                    </h5>
                                    <p class="card-text">عدد المصادر</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- تحليل حسب المصدر --}}
                    @if (!empty($reportData['leads_by_source']))
                        <div class="mb-4">
                            <h5>التوزيع حسب المصدر</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>المصدر</th>
                                            <th>عدد الفرص</th>
                                            <th>النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reportData['leads_by_source'] as $source => $count)
                                            <tr>
                                                <td>{{ $source ?: 'غير محدد' }}</td>
                                                <td>{{ $count }}</td>
                                                <td>{{ $reportData['total_leads'] > 0 ? round(($count / $reportData['total_leads']) * 100, 1) : 0 }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- تحليل حسب المسؤول --}}
                    @if (!empty($reportData['leads_by_user']))
                        <div class="mb-4">
                            <h5>التوزيع حسب المسؤول</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>المسؤول</th>
                                            <th>عدد الفرص</th>
                                            <th>النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reportData['leads_by_user'] as $user => $count)
                                            <tr>
                                                <td>{{ $user ?: 'غير مُعين' }}</td>
                                                <td>{{ $count }}</td>
                                                <td>{{ $reportData['total_leads'] > 0 ? round(($count / $reportData['total_leads']) * 100, 1) : 0 }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- تفاصيل الفرص --}}
                    <div class="mb-4">
                        <h5>تفاصيل الفرص</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>العنوان</th>
                                        <th>العميل</th>
                                        <th>القيمة</th>
                                        <th>المصدر</th>
                                        <th>المسؤول</th>
                                        <th>تاريخ الإنشاء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData['leads_details'] as $lead)
                                        <tr>
                                            <td>{{ $lead['title'] }}</td>
                                            <td>{{ $lead['client_name'] }}</td>
                                            <td>{{ $lead['amount'] ? number_format($lead['amount']) . ' ج.م' : '-' }}
                                            </td>
                                            <td>{{ $lead['source'] }}</td>
                                            <td>{{ $lead['assigned_to'] }}</td>
                                            <td>{{ $lead['created_at'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">إغلاق</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">طباعة
                            التقرير</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const board = document.getElementById('leads-board');
                // const scrollLeftIndicator = document.getElementById('scroll-left');
                const scrollRightIndicator = document.getElementById('scroll-right');

                let draggedElement = null;
                let scrollInterval = null;
                let isDragging = false;

                // إعداد أزرار التحكم في الاسكرول اليدوي
                const scrollLeftBtn = document.createElement('button');
                scrollLeftBtn.className = 'scroll-btn left';
                scrollLeftBtn.innerHTML = '◀';
                scrollLeftBtn.onclick = () => board.scrollBy({
                    left: -300,
                    behavior: 'smooth'
                });
                const scrollRightBtn = document.createElement('button');
                scrollRightBtn.className = 'scroll-btn right';
                scrollRightBtn.innerHTML = '▶';
                scrollRightBtn.onclick = () => board.scrollBy({
                    left: 300,
                    behavior: 'smooth'
                });

                board.parentNode.style.position = 'relative';
                board.parentNode.appendChild(scrollLeftBtn);
                board.parentNode.appendChild(scrollRightBtn);

                setupDragAndDrop();

                function setupDragAndDrop() {
                    document.querySelectorAll('.lead-card').forEach(card => {
                        card.addEventListener('dragstart', handleDragStart);
                        card.addEventListener('dragend', handleDragEnd);
                        card.addEventListener('touchstart', handleTouchStart, {
                            passive: false
                        });
                        card.addEventListener('touchmove', handleTouchMove, {
                            passive: false
                        });
                        card.addEventListener('touchend', handleTouchEnd, {
                            passive: false
                        });
                    });

                    document.querySelectorAll('.status-column').forEach(column => {
                        column.addEventListener('dragover', throttle(handleDragOver, 10));
                        column.addEventListener('drop', handleDrop);
                        column.addEventListener('dragenter', handleDragEnter);
                        column.addEventListener('dragleave', handleDragLeave);
                        column.addEventListener('touchmove', throttle(handleTouchMove, 10), {
                            passive: false
                        });
                    });
                }

                function handleDragStart(e) {
                    draggedElement = this;
                    isDragging = true;
                    this.classList.add('dragging');
                    const clone = this.cloneNode(true);
                    clone.classList.add('drag-placeholder');
                    this.parentNode.insertBefore(clone, this.nextSibling);
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.outerHTML);
                    startScrollMonitoring();
                }

                function handleDragEnd(e) {
                    this.classList.remove('dragging');
                    const placeholder = document.querySelector('.drag-placeholder');
                    if (placeholder) placeholder.remove();
                    document.querySelectorAll('.status-column').forEach(col => {
                        col.classList.remove('dragover', 'drag-active');
                    });
                    draggedElement = null;
                    isDragging = false;
                    stopScrollMonitoring();
                }

                function handleDragOver(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    updateScrollBasedOnMousePosition(e);
                    return false;
                }

                function handleDragEnter(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                    document.querySelectorAll('.status-column').forEach(col => {
                        if (col !== this) col.classList.add('drag-active');
                    });
                }

                function handleDragLeave(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX;
                    const y = e.clientY;
                    if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                        this.classList.remove('dragover');
                    }
                }

                function handleDrop(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    this.classList.remove('dragover');
                    document.querySelectorAll('.status-column').forEach(col => {
                        col.classList.remove('drag-active');
                    });
                    if (draggedElement) {
                        const leadId = draggedElement.getAttribute('data-lead-id');
                        const newStatusId = this.getAttribute('data-status-id');
                        this.style.transform = 'scale(1.05)';
                        setTimeout(() => this.style.transform = '', 200);
                        @this.updateLeadStatus(leadId, newStatusId);
                    }
                    return false;
                }

                function handleTouchStart(e) {
                    e.preventDefault();
                    draggedElement = this;
                    isDragging = true;
                    this.classList.add('dragging');
                    const clone = this.cloneNode(true);
                    clone.classList.add('drag-placeholder');
                    this.parentNode.insertBefore(clone, this.nextSibling);
                    startScrollMonitoring();
                }

                function handleTouchMove(e) {
                    e.preventDefault();
                    const touch = e.touches[0];
                    updateScrollBasedOnTouchPosition(touch);
                    const target = document.elementFromPoint(touch.clientX, touch.clientY);
                    if (target && target.closest('.status-column')) {
                        target.closest('.status-column').classList.add('dragover');
                    }
                }

                function handleTouchEnd(e) {
                    e.preventDefault();
                    const touch = e.changedTouches[0];
                    const target = document.elementFromPoint(touch.clientX, touch.clientY);
                    if (target && target.closest('.status-column')) {
                        const leadId = draggedElement.getAttribute('data-lead-id');
                        const newStatusId = target.closest('.status-column').getAttribute('data-status-id');
                        target.closest('.status-column').style.transform = 'scale(1.05)';
                        setTimeout(() => target.closest('.status-column').style.transform = '', 200);
                        @this.updateLeadStatus(leadId, newStatusId);
                    }
                    draggedElement.classList.remove('dragging');
                    const placeholder = document.querySelector('.drag-placeholder');
                    if (placeholder) placeholder.remove();
                    document.querySelectorAll('.status-column').forEach(col => {
                        col.classList.remove('dragover', 'drag-active');
                    });
                    draggedElement = null;
                    isDragging = false;
                    stopScrollMonitoring();
                }

                function updateScrollBasedOnMousePosition(e) {
                    const boardRect = board.getBoundingClientRect();
                    const mouseX = e.clientX;
                    const scrollZone = 80;
                    const leftZone = boardRect.left + scrollZone;
                    const rightZone = boardRect.right - scrollZone;

                    const maxScrollSpeed = 20;
                    let scrollSpeed = 0;

                    if (mouseX < leftZone) {
                        scrollSpeed = -maxScrollSpeed * ((leftZone - mouseX) / scrollZone);
                        showScrollIndicator('left');
                        startAutoScroll('left', scrollSpeed);
                    } else if (mouseX > rightZone) {
                        scrollSpeed = maxScrollSpeed * ((mouseX - rightZone) / scrollZone);
                        showScrollIndicator('right');
                        startAutoScroll('right', scrollSpeed);
                    } else {
                        hideScrollIndicators();
                        stopAutoScroll();
                    }
                }

                function updateScrollBasedOnTouchPosition(touch) {
                    const boardRect = board.getBoundingClientRect();
                    const touchX = touch.clientX;
                    const scrollZone = 80;
                    const leftZone = boardRect.left + scrollZone;
                    const rightZone = boardRect.right - scrollZone;

                    const maxScrollSpeed = 20;
                    let scrollSpeed = 0;

                    if (touchX < leftZone) {
                        scrollSpeed = -maxScrollSpeed * ((leftZone - touchX) / scrollZone);
                        showScrollIndicator('left');
                        startAutoScroll('left', scrollSpeed);
                    } else if (touchX > rightZone) {
                        scrollSpeed = maxScrollSpeed * ((touchX - rightZone) / scrollZone);
                        showScrollIndicator('right');
                        startAutoScroll('right', scrollSpeed);
                    } else {
                        hideScrollIndicators();
                        stopAutoScroll();
                    }
                }

                function startAutoScroll(direction, speed) {
                    stopAutoScroll();
                    scrollInterval = setInterval(() => {
                        board.scrollLeft += direction === 'left' ? speed : speed;
                    }, 16);
                }

                function stopAutoScroll() {
                    if (scrollInterval) {
                        clearInterval(scrollInterval);
                        scrollInterval = null;
                    }
                }

                function showScrollIndicator(direction) {
                    hideScrollIndicators();
                    const indicator = direction === 'left' ? scrollLeftIndicator : scrollRightIndicator;
                    indicator.style.display = 'flex';
                }

                function hideScrollIndicators() {
                    scrollLeftIndicator.style.display = 'none';
                    scrollRightIndicator.style.display = 'none';
                }

                function startScrollMonitoring() {
                    document.addEventListener('dragover', updateScrollBasedOnMousePosition);
                }

                function stopScrollMonitoring() {
                    document.removeEventListener('dragover', updateScrollBasedOnMousePosition);
                    stopAutoScroll();
                    hideScrollIndicators();
                }

                function throttle(func, limit) {
                    let inThrottle;
                    return function() {
                        const args = arguments;
                        const context = this;
                        if (!inThrottle) {
                            func.apply(context, args);
                            inThrottle = true;
                            setTimeout(() => inThrottle = false, limit);
                        }
                    }
                }

                // مراقبة التغييرات في DOM لإضافة أحداث للكروت الجديدة
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.addedNodes.length) {
                            requestAnimationFrame(() => setupDragAndDrop());
                        }
                    });
                });
                observer.observe(board, {
                    childList: true,
                    subtree: true
                });

                Livewire.on('lead-moved', () => {
                    requestAnimationFrame(() => setupDragAndDrop());
                });

                Livewire.on('lead-added', () => {
                    requestAnimationFrame(() => setupDragAndDrop());
                });

                document.addEventListener('livewire:updated', () => {
                    requestAnimationFrame(() => setupDragAndDrop());
                });
            });
        </script>
    @endpush
</div>

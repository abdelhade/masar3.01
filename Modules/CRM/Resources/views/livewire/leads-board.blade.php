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

            /* مؤشرات السكرول */
            .scroll-indicator {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 50px;
                height: 100px;
                background: rgba(0, 123, 255, 0.8);
                border-radius: 25px;
                display: none;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 20px;
                z-index: 999;
                animation: pulse 1s infinite;
            }

            .scroll-indicator.left {
                left: 10px;
            }

            .scroll-indicator.right {
                right: 10px;
            }

            @keyframes pulse {
                0% {
                    opacity: 0.7;
                }

                50% {
                    opacity: 1;
                }

                100% {
                    opacity: 0.7;
                }
            }

            /* تحسين الأداء */
            .leads-container {
                contain: layout style paint;
                will-change: scroll-position;
            }

            .lead-card {
                contain: layout;
                will-change: transform, opacity;
            }
        </style>
    @endpush

    <div class="container-fluid">
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="leads-board d-flex flex-row flex-nowrap" id="leads-board"
            style="max-height: 80vh; overflow-x: auto; overflow-y: hidden; white-space: nowrap; scrollbar-width: thin; position: relative;">

            <!-- مؤشرات السكرول -->
            <div class="scroll-indicator left" id="scroll-left">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="scroll-indicator right" id="scroll-right">
                <i class="fas fa-chevron-right"></i>
            </div>

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
                            @can('اضافه فرصه')
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
                                            <i class="fas fa-money-bill"></i> {{ number_format($lead['amount'], 2) }}
                                            ج.م
                                        </div>
                                    @endif
                                    @if ($lead['assigned_to'])
                                        <div class="lead-info">
                                            <i class="fas fa-user-tie"></i> {{ $lead['assigned_to']['name'] }}
                                        </div>
                                    @endif
                                    <div class="lead-actions">
                                        @can('حذف الفرصه')
                                        <button class="btn btn-danger btn-sm"
                                            wire:click="deleteLead({{ $lead['id'] }})"
                                            onclick="return confirm('هل أنت متأكد من حذف هذه الفرصة؟')">
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
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const board = document.getElementById('leads-board');
                const scrollLeftIndicator = document.getElementById('scroll-left');
                const scrollRightIndicator = document.getElementById('scroll-right');

                let draggedElement = null;
                let scrollInterval = null;
                let isDragging = false;

                // إعداد drag & drop
                setupDragAndDrop();

                function setupDragAndDrop() {
                    // إعداد السحب للكروت مع debouncing
                    document.querySelectorAll('.lead-card').forEach(card => {
                        card.addEventListener('dragstart', handleDragStart);
                        card.addEventListener('dragend', handleDragEnd);
                    });

                    // إعداد الإسقاط للأعمدة
                    document.querySelectorAll('.status-column').forEach(column => {
                        column.addEventListener('dragover', throttle(handleDragOver, 16)); // 60fps
                        column.addEventListener('drop', handleDrop);
                        column.addEventListener('dragenter', handleDragEnter);
                        column.addEventListener('dragleave', handleDragLeave);
                    });
                }

                function handleDragStart(e) {
                    draggedElement = this;
                    isDragging = true;
                    this.classList.add('dragging');

                    // إنشاء نسخة شبحية
                    const clone = this.cloneNode(true);
                    clone.classList.add('drag-placeholder');
                    this.parentNode.insertBefore(clone, this.nextSibling);

                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.outerHTML);

                    // بدء مراقبة السكرول
                    startScrollMonitoring();
                }

                function handleDragEnd(e) {
                    this.classList.remove('dragging');

                    // إزالة النسخة الشبحية
                    const placeholder = document.querySelector('.drag-placeholder');
                    if (placeholder) {
                        placeholder.remove();
                    }

                    // إزالة جميع classes الخاصة بالسحب
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

                    // تحديث موقع الماوس للسكرول
                    updateScrollBasedOnMousePosition(e);

                    return false;
                }

                function handleDragEnter(e) {
                    e.preventDefault();
                    this.classList.add('dragover');

                    // إضافة تأثير للأعمدة المجاورة
                    document.querySelectorAll('.status-column').forEach(col => {
                        if (col !== this) {
                            col.classList.add('drag-active');
                        }
                    });
                }

                function handleDragLeave(e) {
                    // التحقق من أن الماوس خرج فعلاً من العمود
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

                        // إضافة تأثير بصري للتأكيد
                        this.style.transform = 'scale(1.05)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 200);

                        // استدعاء دالة Livewire لتحديث الحالة
                        @this.updateLeadStatus(leadId, newStatusId);
                    }

                    return false;
                }

                // دالة السكرول التلقائي
                function updateScrollBasedOnMousePosition(e) {
                    const boardRect = board.getBoundingClientRect();
                    const mouseX = e.clientX;
                    const scrollZone = 100; // منطقة السكرول بالبكسل

                    const leftZone = boardRect.left + scrollZone;
                    const rightZone = boardRect.right - scrollZone;

                    if (mouseX < leftZone) {
                        // السكرول لليسار
                        showScrollIndicator('left');
                        startAutoScroll('left');
                    } else if (mouseX > rightZone) {
                        // السكرول لليمين
                        showScrollIndicator('right');
                        startAutoScroll('right');
                    } else {
                        // إيقاف السكرول
                        hideScrollIndicators();
                        stopAutoScroll();
                    }
                }

                function startAutoScroll(direction) {
                    stopAutoScroll(); // إيقاف أي سكرول سابق

                    scrollInterval = setInterval(() => {
                        const scrollAmount = 5;
                        if (direction === 'left') {
                            board.scrollLeft -= scrollAmount;
                        } else {
                            board.scrollLeft += scrollAmount;
                        }
                    }, 16); // 60fps
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

                // دالة throttle لتحسين الأداء
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

                // إعادة إعداد drag & drop بعد تحديث Livewire
                Livewire.on('lead-moved', () => {
                    requestAnimationFrame(() => {
                        setupDragAndDrop();
                    });
                });

                // إعادة إعداد drag & drop بعد كل تحديث لـ Livewire
                document.addEventListener('livewire:updated', () => {
                    requestAnimationFrame(() => {
                        setupDragAndDrop();
                    });
                });

                // تحسين الأداء: استخدام Intersection Observer للكروت المرئية فقط
                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.style.willChange = 'transform, opacity';
                            } else {
                                entry.target.style.willChange = 'auto';
                            }
                        });
                    });
                    document.querySelectorAll('.lead-card').forEach(card => {
                        observer.observe(card);
                    });
                }
            });
        </script>
    @endpush
</div>

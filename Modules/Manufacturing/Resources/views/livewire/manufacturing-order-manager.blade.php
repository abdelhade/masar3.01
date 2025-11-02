<div>
    <div class="container-fluid">
        {{-- Alert Messages --}}
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="las la-check-circle me-2"></i>
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="las la-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="las la-exclamation-triangle me-2"></i>
                <strong>يوجد أخطاء في النموذج:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- Stage Management View --}}
        @if ($view_mode === 'stages' && isset($viewing_order))
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1 fw-bold">
                                    <i class="las la-tasks me-2"></i>
                                    مراحل أمر التصنيع: {{ $viewing_order->order_number }}
                                </h4>
                                <small class="opacity-75">الفرع:
                                    {{ $viewing_order->branch->name ?? '-' }}</small>
                            </div>
                            <button wire:click="backToList" class="btn btn-light btn-sm">
                                <i class="las la-arrow-right me-1"></i> رجوع
                            </button>
                        </div>

                        <div class="card-body p-0">
                            {{-- Order Summary --}}
                            <div class="row g-0 border-bottom">
                                <div class="col-md-3 border-end p-3 text-center">
                                    <div class="text-muted small mb-1">إجمالي المراحل</div>
                                    <div class="fs-3 fw-bold text-primary">{{ $viewing_order->stages->count() }}
                                    </div>
                                </div>
                                <div class="col-md-3 border-end p-3 text-center">
                                    <div class="text-muted small mb-1">إجمالي الكمية</div>
                                    <div class="fs-3 fw-bold text-success">
                                        {{ number_format($viewing_order->stages->sum('pivot.quantity')) }}
                                    </div>
                                </div>
                                <div class="col-md-3 border-end p-3 text-center">
                                    <div class="text-muted small mb-1">المدة المقدرة</div>
                                    <div class="fs-3 fw-bold text-info">
                                        {{ number_format($viewing_order->estimated_duration, 1) }}</div>
                                    <small class="text-muted">ساعة</small>
                                </div>
                                <div class="col-md-3 p-3 text-center">
                                    <div class="text-muted small mb-1">حالة الأمر</div>
                                    @php
                                        $orderStatusBadge = [
                                            'draft' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $orderStatusText = [
                                            'draft' => 'مسودة',
                                            'in_progress' => 'قيد التنفيذ',
                                            'completed' => 'مكتمل',
                                            'cancelled' => 'ملغي',
                                        ];
                                    @endphp
                                    <span
                                        class="badge bg-{{ $orderStatusBadge[$viewing_order->status] ?? 'secondary' }} fs-6 px-3 py-2">
                                        {{ $orderStatusText[$viewing_order->status] ?? $viewing_order->status }}
                                    </span>
                                </div>
                            </div>

                            {{-- Stages Timeline --}}
                            <div class="p-4">
                                <div class="timeline">
                                    @foreach ($viewing_order->stages as $index => $stage)
                                        @php
                                            $statusEnum = \Modules\Manufacturing\Enums\ManufacturingStageStatus::from(
                                                $stage->pivot->status,
                                            );
                                            $isCompleted = $stage->pivot->status === 'completed';
                                            $isInProgress = $stage->pivot->status === 'in_progress';
                                        @endphp

                                        <div class="timeline-item mb-4 position-relative">
                                            <div class="row g-0 align-items-center">
                                                {{-- Timeline Icon --}}
                                                <div class="col-auto">
                                                    <div class="timeline-badge bg-{{ $statusEnum->color() }} text-white d-flex align-items-center justify-content-center rounded-circle"
                                                        style="width: 50px; height: 50px;">
                                                        <i class="las {{ $statusEnum->icon() }} fs-3"></i>
                                                    </div>
                                                    @if (!$loop->last)
                                                        <div class="timeline-line bg-{{ $isCompleted ? 'success' : 'secondary' }}"
                                                            style="width: 3px; height: 80px; margin: 5px auto;">
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Stage Card --}}
                                                <div class="col">
                                                    <div
                                                        class="card border-{{ $statusEnum->color() }} ms-3 {{ $isInProgress ? 'shadow' : '' }}">
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-6">
                                                                    <h5 class="mb-2 fw-bold">
                                                                        <span
                                                                            class="badge bg-light text-dark me-2">{{ $index + 1 }}</span>
                                                                        {{ $stage->name }}
                                                                    </h5>
                                                                    @if ($stage->pivot->notes)
                                                                        <p class="text-muted small mb-2">
                                                                            <i class="las la-comment me-1"></i>
                                                                            {{ $stage->pivot->notes }}
                                                                        </p>
                                                                    @endif
                                                                    <div class="d-flex gap-3 flex-wrap">
                                                                        <span class="badge bg-light text-dark">
                                                                            <i class="las la-bullseye me-1"></i>
                                                                            الكمية المستهدفة:
                                                                            {{ number_format($stage->pivot->quantity) }}
                                                                        </span>

                                                                        @if ($stage->pivot->final_quantity !== null)
                                                                            <span
                                                                                class="badge bg-{{ $stage->pivot->final_quantity >= $stage->pivot->quantity ? 'success' : 'danger' }} text-white">
                                                                                <i class="las la-check-circle me-1"></i>
                                                                                الكمية النهائية:
                                                                                {{ number_format($stage->pivot->final_quantity) }}
                                                                            </span>
                                                                        @endif
                                                                        <span class="badge bg-light text-dark">
                                                                            <i class="las la-clock me-1"></i>
                                                                            {{ number_format($stage->pivot->estimated_duration, 1) }}
                                                                            ساعة
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6 text-md-end mt-3 mt-md-0">

                                                                    <a href="{{ route('manufacturing.create', ['order_id' => $viewing_order->id, 'stage_id' => $stage->id]) }}"
                                                                        class="btn btn-sm btn-success"
                                                                        title="إنشاء فاتورة تصنيع">
                                                                        <i class="las la-file-invoice"></i>
                                                                        <span class="d-none d-lg-inline ms-1">انشاء
                                                                            فاتورة</span>
                                                                    </a>

                                                                    <div class="btn-group" role="group">
                                                                        @foreach (\Modules\Manufacturing\Enums\ManufacturingStageStatus::cases() as $status)
                                                                            <button type="button"
                                                                                wire:click="updateStageStatus({{ $viewing_order->id }}, {{ $stage->id }}, '{{ $status->value }}')"
                                                                                class="btn btn-sm btn-{{ $stage->pivot->status === $status->value ? $status->color() : 'outline-' . $status->color() }}"
                                                                                title="{{ $status->label() }}">
                                                                                <i
                                                                                    class="las {{ $status->icon() }}"></i>
                                                                                <span
                                                                                    class="d-none d-lg-inline ms-1">{{ $status->label() }}</span>
                                                                            </button>
                                                                        @endforeach


                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Form View --}}
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header">
                            <h1 class="mb-0 fw-bold">
                                <i class="las la-{{ $order_id ? 'edit' : 'plus-circle' }} me-2"></i>
                                {{ $order_id ? 'تعديل أمر تصنيع' : 'إنشاء أمر تصنيع جديد' }}
                            </h1>
                        </div>

                        <div class="card-body p-4">
                            <form wire:submit.prevent="{{ $order_id ? 'updateOrder' : 'createOrder' }}">
                                {{-- Basic Info --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="las la-hashtag me-1"></i>رقم الأمر
                                        </label>
                                        <input wire:model="order_number" type="text"
                                            class="form-control @error('order_number') is-invalid @enderror"
                                            {{ $order_id ? '' : 'readonly' }}>
                                        @error('order_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <livewire:app::searchable-select :model="App\Models\Item::class" label-field="name"
                                            :selected-id="$item_id" wire-model="item_id" label="الصنف/المنتج"
                                            placeholder="ابحث عن الصنف أو أضف جديد..." :key="'product-select-' . ($order_id ?? 'new') . '-' . $item_id"
                                            :additional-data="['code' => $this->generateItemCode()]" />
                                        @error('item_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="las la-building me-1"></i>الفرع
                                        </label>
                                        <select wire:model="branch_id"
                                            class="form-select @error('branch_id') is-invalid @enderror">
                                            <option value="">اختر الفرع</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="las la-info-circle me-1"></i>الحالة
                                        </label>
                                        <select wire:model="status"
                                            class="form-select @error('status') is-invalid @enderror">
                                            <option value="draft">مسودة</option>
                                            <option value="in_progress">قيد التنفيذ</option>
                                            <option value="completed">مكتمل</option>
                                            <option value="cancelled">ملغي</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="las la-align-left me-1"></i>الوصف
                                        </label>
                                        <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="2"></textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Template Option --}}
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" wire:model.live="is_template"
                                                        class="form-check-input" id="saveTemplate">
                                                    <label for="saveTemplate" class="form-check-label fw-semibold">
                                                        <i class="las la-save me-1"></i>حفظ كقالب
                                                    </label>
                                                </div>
                                                @if ($is_template)
                                                    <div class="mt-3">
                                                        <input wire:model="template_name" type="text"
                                                            placeholder="اسم القالب"
                                                            class="form-control @error('template_name') is-invalid @enderror">
                                                        @error('template_name')
                                                            <div class="invalid-feedback">{{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if (count($templates) > 0)
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <label class="form-label fw-semibold">
                                                        <i class="las la-file-import me-1"></i>تحميل قالب جاهز
                                                    </label>
                                                    <select wire:change="loadTemplate($event.target.value)"
                                                        class="form-select">
                                                        <option value="">اختر القالب</option>
                                                        @foreach ($templates as $template)
                                                            <option value="{{ $template->id }}">
                                                                {{ $template->template_name }}
                                                                ({{ $template->stages->count() }} مرحلة)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Stages Section --}}
                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-opacity-10 border-primary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 fw-bold text-primary">
                                                <i class="las la-layer-group me-2"></i>مراحل التصنيع
                                            </h5>
                                            @if (!empty($selected_stages))
                                                <span class="badge bg-primary">{{ count($selected_stages) }}
                                                    مرحلة</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-8">
                                                <select wire:change="addStage($event.target.value)"
                                                    class="form-select form-select-lg" id="stageSelect">
                                                    <option value="">
                                                        <i class="las la-plus-circle"></i> اختر مرحلة لإضافتها
                                                    </option>
                                                    @foreach ($available_stages as $stage)
                                                        <option value="{{ $stage->id }}">
                                                            {{ $stage->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        @if (empty($selected_stages))
                                            <div class="alert alert-info d-flex align-items-center mb-0"
                                                role="alert">
                                                <i class="las la-info-circle fs-3 me-3"></i>
                                                <div>
                                                    <strong>لم يتم إضافة مراحل بعد</strong>
                                                    <p class="mb-0 small">يرجى اختيار مرحلة واحدة على الأقل من
                                                        القائمة
                                                        أعلاه</p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="5%" class="text-center">#</th>
                                                            <th width="25%">المرحلة</th>
                                                            <th width="15%">الكمية المستهدفة</th>
                                                            <th width="15%">المدة (ساعات)</th>
                                                            <th width="20%">الحالة</th>
                                                            <th width="15%">ملاحظات</th>
                                                            <th width="5%" class="text-center">إجراء</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($selected_stages as $index => $stage)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <span
                                                                        class="badge bg-dark">{{ $index + 1 }}</span>
                                                                </td>
                                                                <td>
                                                                    <strong
                                                                        class="text-dark">{{ $stage['name'] ?? 'غير محدد' }}</strong>
                                                                </td>
                                                                <td>
                                                                    <input
                                                                        wire:model="selected_stages.{{ $index }}.quantity"
                                                                        type="number" min="0"
                                                                        class="form-control form-control-sm @error('selected_stages.' . $index . '.quantity') is-invalid @enderror">
                                                                    @error('selected_stages.' . $index . '.quantity')
                                                                        <div class="invalid-feedback">
                                                                            {{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input
                                                                        wire:model="selected_stages.{{ $index }}.estimated_duration"
                                                                        type="number" step="0.01" min="0"
                                                                        class="form-control form-control-sm @error('selected_stages.' . $index . '.estimated_duration') is-invalid @enderror">
                                                                    @error('selected_stages.' . $index .
                                                                        '.estimated_duration')
                                                                        <div class="invalid-feedback">
                                                                            {{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <select
                                                                        wire:model="selected_stages.{{ $index }}.status"
                                                                        class="form-select form-select-sm @error('selected_stages.' . $index . '.status') is-invalid @enderror">
                                                                        @foreach ($stage_statuses as $status)
                                                                            <option value="{{ $status->value }}">
                                                                                {{ $status->label() }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('selected_stages.' . $index . '.status')
                                                                        <div class="invalid-feedback">
                                                                            {{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input
                                                                        wire:model="selected_stages.{{ $index }}.notes"
                                                                        type="text" placeholder="ملاحظات"
                                                                        class="form-control form-control-sm">
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        wire:click="removeStage({{ $index }})"
                                                                        class="btn btn-sm btn-danger"
                                                                        onclick="return confirm('هل أنت متأكد من حذف هذه المرحلة؟')">
                                                                        <i class="las la-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr class="fw-bold">
                                                            <td colspan="2" class="text-end">الإجمالي:</td>
                                                            <td class="text-primary">
                                                                {{ number_format(collect($selected_stages)->sum('quantity')) }}
                                                            </td>
                                                            <td class="text-info">
                                                                {{ number_format(collect($selected_stages)->sum('estimated_duration'), 2) }}
                                                                ساعة
                                                            </td>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-lg px-4">
                                        <i class="las la-{{ $order_id ? 'save' : 'plus-circle' }} me-2"></i>
                                        {{ $order_id ? 'تحديث الأمر' : 'إنشاء الأمر' }}
                                    </button>
                                    <button type="button" wire:click="resetForm"
                                        class="btn btn-secondary btn-lg px-4">
                                        <i class="las la-redo me-2"></i>إعادة تعيين
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orders List --}}
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0 fw-bold">
                                <i class="las la-list me-2"></i>قائمة أوامر التصنيع
                            </h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>رقم الأمر</th>
                                            <th>الفرع</th>
                                            <th class="text-center">الحالة</th>
                                            <th class="text-center">عدد المراحل</th>
                                            <th class="text-end">الكميه</th>
                                            <th class="text-end">المدة المقدرة</th>
                                            <th class="text-center">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($orders as $order)
                                            <tr>
                                                <td>
                                                    <strong class="text-primary">{{ $order->order_number }}</strong>
                                                    @if ($order->is_template)
                                                        <span class="badge bg-info ms-2">
                                                            <i class="las la-file-alt"></i> قالب
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <i class="las la-building text-muted me-1"></i>
                                                    {{ $order->branch->name ?? '-' }}
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $statusClass = [
                                                            'draft' => 'warning',
                                                            'in_progress' => 'info',
                                                            'completed' => 'success',
                                                            'cancelled' => 'danger',
                                                        ];
                                                        $statusText = [
                                                            'draft' => 'مسودة',
                                                            'in_progress' => 'قيد التنفيذ',
                                                            'completed' => 'مكتمل',
                                                            'cancelled' => 'ملغي',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $statusClass[$order->status] ?? 'secondary' }} px-3 py-2">
                                                        {{ $statusText[$order->status] ?? $order->status }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-dark">{{ $order->stages->count() }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <strong
                                                        class="text-success">{{ number_format($order->stages->sum('pivot.quantity')) }}</strong>
                                                </td>
                                                <td class="text-end">
                                                    <strong
                                                        class="text-info">{{ number_format($order->estimated_duration, 1) }}</strong>
                                                    <small class="text-muted">ساعة</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="viewOrderStages({{ $order->id }})"
                                                            class="btn btn-sm btn-info" title="عرض المراحل">
                                                            <i class="las la-tasks"></i>
                                                        </button>
                                                        <button wire:click="editOrder({{ $order->id }})"
                                                            class="btn btn-sm btn-primary" title="تعديل">
                                                            <i class="las la-edit"></i>
                                                        </button>
                                                        <button wire:click="deleteOrder({{ $order->id }})"
                                                            class="btn btn-sm btn-danger">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <i class="las la-inbox fs-1 text-muted d-block mb-3"></i>
                                                    <p class="text-muted mb-0">لا توجد أوامر تصنيع حالياً</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if ($orders->hasPages())
                            <div class="card-footer bg-light">
                                {{ $orders->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', function() {
                Livewire.on('stageAdded', () => {
                    const select = document.getElementById('stageSelect');
                    if (select) select.value = '';
                });

                Livewire.on('templateLoaded', () => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم التحميل',
                            text: 'تم تحميل القالب بنجاح!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('تم تحميل القالب بنجاح!');
                    }
                });

                Livewire.on('orderCreated', () => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الإنشاء',
                            text: 'تم إنشاء أمر التصنيع بنجاح!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('تم إنشاء أمر التصنيع بنجاح!');
                    }
                });

                Livewire.on('orderUpdated', () => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم التحديث',
                            text: 'تم تحديث أمر التصنيع بنجاح!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('تم تحديث أمر التصنيع بنجاح!');
                    }
                });

                Livewire.on('stageStatusUpdated', () => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم التحديث',
                            text: 'تم تحديث حالة المرحلة بنجاح!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert('تم تحديث حالة المرحلة بنجاح!');
                    }
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            .bg-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .bg-gradient-dark {
                background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            }

            .timeline-badge {
                box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.9);
                position: relative;
                z-index: 2;
            }

            .timeline-line {
                opacity: 0.3;
                transition: all 0.3s ease;
            }

            .card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .card:hover {
                transform: translateY(-2px);
            }

            .table tbody tr {
                transition: background-color 0.2s ease;
            }

            .table tbody tr:hover {
                background-color: rgba(0, 123, 255, 0.05);
            }

            .btn-group .btn {
                margin: 0 2px;
            }

            .timeline-item {
                animation: fadeInUp 0.5s ease;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            }

            .badge {
                font-weight: 500;
                letter-spacing: 0.3px;
            }
        </style>
    @endpush
</div>

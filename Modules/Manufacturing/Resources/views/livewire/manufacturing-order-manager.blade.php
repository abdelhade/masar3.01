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
                <strong>{{ __('Form has errors') }}:</strong>
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
                                    {{ __('Manufacturing Order Stages') }}: {{ $viewing_order->order_number }}
                                </h4>
                                <small class="opacity-75">{{ __('Branch') }}:
                                    {{ $viewing_order->branch->name ?? '-' }}</small>
                            </div>
                            <button wire:click="backToList" class="btn btn-light btn-sm">
                                <i class="las la-arrow-right me-1"></i> {{ __('Back') }}
                            </button>
                        </div>

                        <div class="card-body p-0">
                            {{-- Order Summary --}}
                            <div class="row g-0 border-bottom">
                                <div class="col-md-3 border-end p-3 text-center">
                                    <div class="text-muted small mb-1">{{ __('Total Stages') }}</div>
                                    <div class="fs-3 fw-bold text-primary">{{ $viewing_order->stages->count() }}
                                    </div>
                                </div>
                                <div class="col-md-3 border-end p-3 text-center">
                                    <div class="text-muted small mb-1">{{ __('Total Quantity') }}</div>
                                    <div class="fs-3 fw-bold text-success">
                                        {{ number_format($viewing_order->stages->sum('pivot.quantity')) }}
                                    </div>
                                </div>
                                <div class="col-md-3 border-end p-3 text-center">
                                    <div class="text-muted small mb-1">{{ __('Estimated Duration') }}</div>
                                    <div class="fs-3 fw-bold text-info">
                                        {{ number_format($viewing_order->estimated_duration, 1) }}</div>
                                    <small class="text-muted">{{ __('hours') }}</small>
                                </div>
                                <div class="col-md-3 p-3 text-center">
                                    <div class="text-muted small mb-1">{{ __('Order Status') }}</div>
                                    @php
                                        $orderStatusBadge = [
                                            'draft' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $orderStatusText = [
                                            'draft' => __('Draft'),
                                            'in_progress' => __('In Progress'),
                                            'completed' => __('Completed'),
                                            'cancelled' => __('Cancelled'),
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
                                                                            {{ __('Target Quantity') }}:
                                                                            {{ number_format($stage->pivot->quantity) }}
                                                                        </span>

                                                                        @if ($stage->pivot->final_quantity !== null)
                                                                            <span
                                                                                class="badge bg-{{ $stage->pivot->final_quantity >= $stage->pivot->quantity ? 'success' : 'danger' }} text-white">
                                                                                <i class="las la-check-circle me-1"></i>
                                                                                {{ __('Final Quantity') }}:
                                                                                {{ number_format($stage->pivot->final_quantity) }}
                                                                            </span>
                                                                        @endif
                                                                        <span class="badge bg-light text-dark">
                                                                            <i class="las la-clock me-1"></i>
                                                                            {{ number_format($stage->pivot->estimated_duration, 1) }}
                                                                            {{ __('hours') }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6 text-md-end mt-3 mt-md-0">

                                                                    <a href="{{ route('manufacturing.create', ['order_id' => $viewing_order->id, 'stage_id' => $stage->id]) }}"
                                                                        class="btn btn-sm btn-success"
                                                                        title="{{ __('Create Manufacturing Invoice') }}">
                                                                        <i class="las la-file-invoice"></i>
                                                                        <span
                                                                            class="d-none d-lg-inline ms-1">{{ __('Create Invoice') }}</span>
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
                                {{ $order_id ? __('Edit Manufacturing Order') : __('Create New Manufacturing Order') }}
                            </h1>
                        </div>

                        <div class="card-body p-4">
                            <form wire:submit.prevent="{{ $order_id ? 'updateOrder' : 'createOrder' }}">
                                {{-- Basic Info --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="las la-hashtag me-1"></i>{{ __('Order Number') }}
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
                                            :selected-id="$item_id" wire-model="item_id" label="{{ __('Item/Product') }}"
                                            placeholder="{{ __('Search for item or add new...') }}" :key="'product-select-' . ($order_id ?? 'new') . '-' . $item_id"
                                            :additional-data="['code' => $this->generateItemCode()]" />
                                        @error('item_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="las la-building me-1"></i>{{ __('Branch') }}
                                        </label>
                                        <select wire:model="branch_id"
                                            class="form-select @error('branch_id') is-invalid @enderror">
                                            <option value="">{{ __('Select Branch') }}</option>
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
                                            <i class="las la-info-circle me-1"></i>{{ __('Status') }}
                                        </label>
                                        <select wire:model="status"
                                            class="form-select @error('status') is-invalid @enderror">
                                            <option value="draft">{{ __('Draft') }}</option>
                                            <option value="in_progress">{{ __('In Progress') }}</option>
                                            <option value="completed">{{ __('Completed') }}</option>
                                            <option value="cancelled">{{ __('Cancelled') }}</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="las la-align-left me-1"></i>{{ __('Description') }}
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
                                                        <i class="las la-save me-1"></i>{{ __('Save as Template') }}
                                                    </label>
                                                </div>
                                                @if ($is_template)
                                                    <div class="mt-3">
                                                        <input wire:model="template_name" type="text"
                                                            placeholder="{{ __('Template Name') }}"
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
                                                        <i
                                                            class="las la-file-import me-1"></i>{{ __('Load Ready Template') }}
                                                    </label>
                                                    <select wire:change="loadTemplate($event.target.value)"
                                                        class="form-select">
                                                        <option value="">{{ __('Select Template') }}</option>
                                                        @foreach ($templates as $template)
                                                            <option value="{{ $template->id }}">
                                                                {{ $template->template_name }}
                                                                ({{ $template->stages->count() }} {{ __('stages') }})
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
                                                <i
                                                    class="las la-layer-group me-2"></i>{{ __('Manufacturing Stages') }}
                                            </h5>
                                            @if (!empty($selected_stages))
                                                <span class="badge bg-primary">{{ count($selected_stages) }}
                                                    {{ __('stages') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-8">
                                                <select wire:change="addStage($event.target.value)"
                                                    class="form-select form-select-lg" id="stageSelect">
                                                    <option value="">
                                                        <i class="las la-plus-circle"></i>
                                                        {{ __('Select stage to add') }}
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
                                                    <strong>{{ __('No stages added yet') }}</strong>
                                                    <p class="mb-0 small">
                                                        {{ __('Please select at least one stage from the list above') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="5%" class="text-center">#</th>
                                                            <th width="25%">{{ __('Stage') }}</th>
                                                            <th width="15%">{{ __('Target Quantity') }}</th>
                                                            <th width="15%">{{ __('Duration (hours)') }}</th>
                                                            <th width="20%">{{ __('Status') }}</th>
                                                            <th width="15%">{{ __('Notes') }}</th>
                                                            <th width="5%" class="text-center">
                                                                {{ __('Action') }}</th>
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
                                                                        class="text-dark">{{ $stage['name'] ?? __('Not Specified') }}</strong>
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
                                                                        type="text"
                                                                        placeholder="{{ __('Notes') }}"
                                                                        class="form-control form-control-sm">
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        wire:click="removeStage({{ $index }})"
                                                                        class="btn btn-sm btn-danger"
                                                                        onclick="return confirm('{{ __('Are you sure you want to delete this stage?') }}')">
                                                                        <i class="las la-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr class="fw-bold">
                                                            <td colspan="2" class="text-end">{{ __('Total') }}:
                                                            </td>
                                                            <td class="text-primary">
                                                                {{ number_format(collect($selected_stages)->sum('quantity')) }}
                                                            </td>
                                                            <td class="text-info">
                                                                {{ number_format(collect($selected_stages)->sum('estimated_duration'), 2) }}
                                                                {{ __('hours') }}
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
                                        {{ $order_id ? __('Update Order') : __('Create Order') }}
                                    </button>
                                    <button type="button" wire:click="resetForm"
                                        class="btn btn-secondary btn-lg px-4">
                                        <i class="las la-redo me-2"></i>{{ __('Reset') }}
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
                                <i class="las la-list me-2"></i>{{ __('Manufacturing Orders List') }}
                            </h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Order Number') }}</th>
                                            <th>{{ __('Branch') }}</th>
                                            <th class="text-center">{{ __('Status') }}</th>
                                            <th class="text-center">{{ __('Number of Stages') }}</th>
                                            <th class="text-end">{{ __('Quantity') }}</th>
                                            <th class="text-end">{{ __('Estimated Duration') }}</th>
                                            <th class="text-center">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($orders as $order)
                                            <tr>
                                                <td>
                                                    <strong class="text-primary">{{ $order->order_number }}</strong>
                                                    @if ($order->is_template)
                                                        <span class="badge bg-info ms-2">
                                                            <i class="las la-file-alt"></i> {{ __('Template') }}
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
                                                            'draft' => __('Draft'),
                                                            'in_progress' => __('In Progress'),
                                                            'completed' => __('Completed'),
                                                            'cancelled' => __('Cancelled'),
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
                                                    <small class="text-muted">{{ __('hours') }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <button wire:click="viewOrderStages({{ $order->id }})"
                                                            class="btn btn-sm btn-info"
                                                            title="{{ __('View Stages') }}">
                                                            <i class="las la-tasks"></i>
                                                        </button>
                                                        <button wire:click="editOrder({{ $order->id }})"
                                                            class="btn btn-sm btn-primary"
                                                            title="{{ __('Edit') }}">
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
                                                    <p class="text-muted mb-0">{{ __('No Manufacturing Orders') }}
                                                    </p>
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
                            title: '{{ __('Loaded') }}',
                            text: '{{ __('Template loaded successfully!') }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('{{ __('Template loaded successfully!') }}');
                    }
                });

                Livewire.on('orderCreated', () => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('Created') }}',
                            text: '{{ __('Manufacturing order created successfully!') }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('{{ __('Manufacturing order created successfully!') }}');
                    }
                });

                Livewire.on('orderUpdated', () => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('Updated') }}',
                            text: '{{ __('Manufacturing order updated successfully!') }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('{{ __('Manufacturing order updated successfully!') }}');
                    }
                });

                Livewire.on('stageStatusUpdated', () => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('Updated') }}',
                            text: '{{ __('Stage status updated successfully!') }}',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert('{{ __('Stage status updated successfully!') }}');
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

@extends('admin.dashboard')

@section('body_class', 'invoice-page-fixed')

@section('sidebar')
    @if (in_array($type ?? 10, [10, 12, 14, 16, 22, 26]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($type ?? 10, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($type ?? 10, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection

@push('styles')
    <style>
        [x-cloak] { display: none !important; }

        /* NO PAGE SCROLL - Fixed layout components */
        body.invoice-page-fixed {
            height: 100vh !important;
            overflow: hidden !important;
        }
        
        .invoice-page-fixed .page-wrapper {
            height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        .invoice-page-fixed .page-content {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 0 !important;
            margin-top: 52px !important;
        }

        .invoice-page-fixed .container-fluid,
        .invoice-page-fixed .container-fluid > .row {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .invoice-page-fixed [x-data] {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        .invoice-page-fixed form {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        .invoice-scroll-container {
            flex: 1 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding: 15px !important;
        }

        .invoice-footer-container {
            flex-shrink: 0 !important;
            background: #fff;
            border-top: 2px solid #dee2e6;
            padding: 10px 15px !important;
            z-index: 10;
        }

        /* Table styling */
        .table-responsive {
            overflow-x: auto;
        }

        .table th {
            background: linear-gradient(135deg, #a8c0ff 0%, #c5d9ff 100%);
            color: #2c3e50;
            font-weight: 600;
            text-align: center;
            border: 1px solid #90a4ae;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .form-control-sm {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

        /* Search results */
        .search-results {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-height: 300px;
            overflow-y: auto;
        }

        .search-result-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .search-result-item:hover {
            background: #f8f9fa;
        }
    </style>
@endpush

@section('content')
    <div x-data="invoiceForm({
        type: {{ $type ?? 10 }},
        branchId: {{ auth()->user()->branch_id ?? 'null' }},
        invoiceId: {{ $invoiceId }}
    })" x-init="init()">
        
        <!-- Loading State -->
        <div x-show="ui.loading" x-cloak class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('common.loading') }}</span>
            </div>
            <p class="mt-2">{{ __('invoices.loading_invoice_data') }}</p>
        </div>
        
        <!-- Invoice Form -->
        <div x-show="!ui.loading" x-cloak>
            <form @submit.prevent="saveInvoice()">
                
                <!-- Invoice Header -->
                <div class="invoice-scroll-container">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="las la-edit"></i>
                                {{ __('invoices.edit_invoice') }} #<span x-text="invoice.pro_id"></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Account 1 (Customer/Supplier) -->
                                <div class="col-md-3">
                                    <label class="form-label">
                                        <span x-text="[10, 12, 14, 16, 19, 22].includes(invoice.type) ? '{{ __('invoices.customer') }}' : '{{ __('invoices.supplier') }}'"></span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select x-model="invoice.acc1_id" 
                                            @change="onAccountChange($event.target.value)"
                                            class="form-control" required>
                                        <option value="">{{ __('invoices.select_account') }}</option>
                                        <template x-for="account in data.accounts.customers.concat(data.accounts.suppliers)" :key="account.id">
                                            <option :value="account.id" x-text="account.name"></option>
                                        </template>
                                    </select>
                                </div>
                                
                                <!-- Account 2 (Store) -->
                                <div class="col-md-3">
                                    <label class="form-label">
                                        {{ __('invoices.store') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select x-model="invoice.acc2_id" class="form-control" required>
                                        <option value="">{{ __('invoices.select_store') }}</option>
                                        <template x-for="account in data.accounts.cash_accounts" :key="account.id">
                                            <option :value="account.id" x-text="account.name"></option>
                                        </template>
                                    </select>
                                </div>
                                
                                <!-- Date -->
                                <div class="col-md-2">
                                    <label class="form-label">
                                        {{ __('invoices.date') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" x-model="invoice.pro_date" class="form-control" required>
                                </div>
                                
                                <!-- Branch -->
                                <div class="col-md-2">
                                    <label class="form-label">{{ __('invoices.branch') }}</label>
                                    <select x-model="invoice.branch_id" class="form-control">
                                        <template x-for="branch in data.branches" :key="branch.id">
                                            <option :value="branch.id" x-text="branch.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Serial Number -->
                                <div class="col-md-2">
                                    <label class="form-label">{{ __('invoices.serial_number') }}</label>
                                    <input type="text" x-model="invoice.serial_number" class="form-control">
                                </div>
                            </div>

                            <!-- Second Row -->
                            <div class="row g-3 mt-2">
                                <!-- Notes -->
                                <div class="col-md-12">
                                    <label class="form-label">{{ __('invoices.notes') }}</label>
                                    <textarea x-model="invoice.notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Items -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label">
                                        <i class="las la-search"></i>
                                        {{ __('invoices.search_items') }}
                                    </label>
                                    <input type="text" 
                                           x-model="ui.searchTerm" 
                                           @input="searchItems()"
                                           @focus="ui.searchResults.length > 0 ? null : searchItems()"
                                           class="form-control" 
                                           placeholder="{{ __('invoices.search_by_name_or_code') }}">
                                    
                                    <!-- Search Results Dropdown -->
                                    <div x-show="ui.searchResults.length > 0" 
                                         @click.away="ui.searchResults = []"
                                         class="search-results"
                                         style="width: 100%;">
                                        <template x-for="item in ui.searchResults" :key="item.id">
                                            <div @click="addItemFromSearch(item.id)" class="search-result-item">
                                                <strong x-text="item.name"></strong>
                                                <small class="text-muted d-block" x-text="'{{ __('invoices.code') }}: ' + item.code"></small>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Invoice Items Table -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="las la-list"></i>
                                {{ __('invoices.invoice_items') }}
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 25%">{{ __('invoices.item_name') }}</th>
                                            <th style="width: 15%">{{ __('invoices.unit') }}</th>
                                            <th style="width: 10%">{{ __('invoices.quantity') }}</th>
                                            <th style="width: 12%">{{ __('invoices.price') }}</th>
                                            <th style="width: 10%">{{ __('invoices.discount') }}</th>
                                            <th style="width: 13%">{{ __('invoices.total') }}</th>
                                            <th style="width: 10%">{{ __('invoices.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in invoiceItems" :key="index">
                                            <tr>
                                                <td class="text-center" x-text="index + 1"></td>
                                                <td>
                                                    <span x-text="item.item_name" class="fw-bold"></span>
                                                    <small class="text-muted d-block" x-text="item.item_code"></small>
                                                </td>
                                                <td>
                                                    <select x-model="item.unit_id" 
                                                            @change="calculateItemTotal(index)"
                                                            class="form-control form-control-sm">
                                                        <template x-for="unit in item.available_units" :key="unit.id">
                                                            <option :value="unit.id" x-text="unit.name"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           x-model.number="item.quantity" 
                                                           @input="calculateItemTotal(index)"
                                                           class="form-control form-control-sm text-end" 
                                                           min="0" step="0.01">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           x-model.number="item.price" 
                                                           @input="calculateItemTotal(index)"
                                                           class="form-control form-control-sm text-end" 
                                                           min="0" step="0.01">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           x-model.number="item.discount" 
                                                           @input="calculateItemTotal(index)"
                                                           class="form-control form-control-sm text-end" 
                                                           min="0" step="0.01">
                                                </td>
                                                <td class="text-end">
                                                    <strong x-text="formatCurrency(item.sub_value)"></strong>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" 
                                                            @click="removeRow(index)"
                                                            class="btn btn-sm btn-danger"
                                                            :disabled="invoiceItems.length === 1">
                                                        <i class="las la-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        
                                        <tr x-show="invoiceItems.length === 0">
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="las la-inbox la-3x d-block mb-2"></i>
                                                {{ __('invoices.no_items_added') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="p-3">
                                <button type="button" @click="addRow()" class="btn btn-sm btn-secondary">
                                    <i class="las la-plus"></i> {{ __('invoices.add_row') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Totals Card -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th class="text-end">{{ __('invoices.subtotal') }}:</th>
                                            <td class="text-end" x-text="formatCurrency(calculations.subtotal)"></td>
                                        </tr>
                                        <tr>
                                            <th class="text-end">{{ __('invoices.discount') }}:</th>
                                            <td class="text-end">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" 
                                                           x-model.number="calculations.discount_percentage" 
                                                           @input="onDiscountPercentageChange()"
                                                           class="form-control text-end" 
                                                           style="max-width: 80px;"
                                                           min="0" max="100" step="0.01">
                                                    <span class="input-group-text">%</span>
                                                    <span class="input-group-text" x-text="formatCurrency(calculations.discount_value)"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-end">{{ __('invoices.vat') }} (<span x-text="calculations.vat_percentage"></span>%):</th>
                                            <td class="text-end" x-text="formatCurrency(calculations.vat_value)"></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <th class="text-end fs-5">{{ __('invoices.total') }}:</th>
                                            <th class="text-end fs-5" x-text="formatCurrency(calculations.total_after_additional)"></th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Footer -->
                <div class="invoice-footer-container">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <button type="submit" 
                                    class="btn btn-primary" 
                                    :disabled="ui.saving">
                                <span x-show="!ui.saving">
                                    <i class="las la-save"></i> {{ __('invoices.update_invoice') }}
                                </span>
                                <span x-show="ui.saving">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    {{ __('invoices.updating') }}...
                                </span>
                            </button>
                            
                            <button type="button" 
                                    @click="saveAndPrint()" 
                                    class="btn btn-warning"
                                    :disabled="ui.saving">
                                <i class="las la-print"></i> {{ __('invoices.save_and_print') }}
                            </button>
                            
                            <a href="{{ route('invoices.index', ['type' => $type]) }}" class="btn btn-secondary">
                                <i class="las la-times"></i> {{ __('invoices.cancel') }}
                            </a>
                        </div>
                        <div class="col-md-6">
                            <div class="text-end">
                                <div class="d-inline-block bg-primary text-white px-4 py-2 rounded">
                                    <small class="d-block">{{ __('invoices.total') }}</small>
                                    <strong class="fs-4" x-text="formatCurrency(calculations.total_after_additional)"></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('Modules/Invoices/Resources/assets/js/invoice-calculations.js') }}"></script>
    <script src="{{ asset('Modules/Invoices/Resources/assets/js/invoice-form.js') }}"></script>
@endpush

@extends('admin.dashboard')

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
                <span class="visually-hidden">{{ __('invoices.loading') }}</span>
            </div>
            <p class="mt-2">{{ __('invoices.loading_data') }}</p>
        </div>
        
        <!-- Invoice Form -->
        <div x-show="!ui.loading" x-cloak>
            <form @submit.prevent="saveInvoice()">
                
                <!-- Invoice Header -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('invoices.edit_invoice') }} #<span x-text="invoiceId"></span></h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Account 1 -->
                            <div class="col-md-3">
                                <label class="form-label">{{ __('invoices.account') }}</label>
                                <select x-model="invoice.acc1_id" 
                                        @change="onAccountChange($event.target.value)"
                                        class="form-control" required>
                                    <option value="">{{ __('invoices.select_account') }}</option>
                                    <template x-for="account in data.accounts.customers" :key="account.id">
                                        <option :value="account.id" x-text="account.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <!-- Account 2 (Store) -->
                            <div class="col-md-3">
                                <label class="form-label">{{ __('invoices.store') }}</label>
                                <select x-model="invoice.acc2_id" class="form-control" required>
                                    <option value="">{{ __('invoices.select_store') }}</option>
                                    <template x-for="account in data.accounts.cash_accounts" :key="account.id">
                                        <option :value="account.id" x-text="account.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <!-- Date -->
                            <div class="col-md-2">
                                <label class="form-label">{{ __('invoices.date') }}</label>
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
                        </div>
                    </div>
                </div>
                
                <!-- Search Items -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('invoices.search_items') }}</label>
                                <input type="text" 
                                       x-model="ui.searchTerm" 
                                       @input.debounce.300ms="searchItems()"
                                       class="form-control" 
                                       placeholder="{{ __('invoices.search_placeholder') }}">
                                
                                <!-- Search Results -->
                                <div x-show="ui.searchResults.length > 0" 
                                     class="list-group mt-2" 
                                     style="max-height: 300px; overflow-y: auto;">
                                    <template x-for="item in ui.searchResults" :key="item.id">
                                        <button type="button" 
                                                @click="addItemFromSearch(item.id)"
                                                class="list-group-item list-group-item-action">
                                            <span x-text="item.name"></span>
                                            <small class="text-muted" x-text="' - ' + item.code"></small>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Invoice Items Table -->
                <div class="card mb-3">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('invoices.item_name') }}</th>
                                    <th>{{ __('invoices.unit') }}</th>
                                    <th>{{ __('invoices.quantity') }}</th>
                                    <th>{{ __('invoices.price') }}</th>
                                    <th>{{ __('invoices.discount') }}</th>
                                    <th>{{ __('invoices.total') }}</th>
                                    <th>{{ __('invoices.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in invoiceItems" :key="index">
                                    <tr>
                                        <td x-text="item.item_name"></td>
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
                                                   class="form-control form-control-sm" 
                                                   min="0" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   x-model.number="item.price" 
                                                   @input="calculateItemTotal(index)"
                                                   class="form-control form-control-sm" 
                                                   min="0" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   x-model.number="item.discount" 
                                                   @input="calculateItemTotal(index)"
                                                   class="form-control form-control-sm" 
                                                   min="0" step="0.01">
                                        </td>
                                        <td>
                                            <span x-text="formatCurrency(item.sub_value)"></span>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    @click="removeRow(index)"
                                                    class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                
                                <tr x-show="invoiceItems.length === 0">
                                    <td colspan="7" class="text-center text-muted">
                                        {{ __('invoices.no_items') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <button type="button" @click="addRow()" class="btn btn-sm btn-secondary">
                            <i class="fas fa-plus"></i> {{ __('invoices.add_row') }}
                        </button>
                    </div>
                </div>
                
                <!-- Totals -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 ms-auto">
                                <table class="table table-sm">
                                    <tr>
                                        <th>{{ __('invoices.subtotal') }}:</th>
                                        <td class="text-end" x-text="formatCurrency(calculations.subtotal)"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('invoices.discount') }}:</th>
                                        <td class="text-end">
                                            <input type="number" 
                                                   x-model.number="calculations.discount_percentage" 
                                                   @input="onDiscountPercentageChange()"
                                                   class="form-control form-control-sm d-inline-block" 
                                                   style="width: 80px;" 
                                                   min="0" max="100" step="0.01">
                                            %
                                            <span x-text="formatCurrency(calculations.discount_value)"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('invoices.vat') }} (<span x-text="calculations.vat_percentage"></span>%):</th>
                                        <td class="text-end" x-text="formatCurrency(calculations.vat_value)"></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th>{{ __('invoices.total') }}:</th>
                                        <th class="text-end" x-text="formatCurrency(calculations.total)"></th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" 
                                class="btn btn-primary" 
                                :disabled="ui.saving">
                            <span x-show="!ui.saving">
                                <i class="fas fa-save"></i> {{ __('invoices.update') }}
                            </span>
                            <span x-show="ui.saving">
                                <span class="spinner-border spinner-border-sm"></span>
                                {{ __('invoices.updating') }}
                            </span>
                        </button>
                        
                        <button type="button" 
                                @click="saveAndPrint()" 
                                class="btn btn-warning"
                                :disabled="ui.saving">
                            <i class="fas fa-print"></i> {{ __('invoices.save_and_print') }}
                        </button>
                        
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('invoices.cancel') }}
                        </a>
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

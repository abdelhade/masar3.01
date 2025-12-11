{{-- Leave Balances Tab --}}
<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="card-title mb-0 font-hold fw-bold d-flex align-items-center">
                <i class="fas fa-calendar-check me-2"></i>{{ __('رصيد الإجازات للموظف') }}
            </h6>
        </div>
        <div class="card-body p-4">
            <!-- إضافة رصيد إجازة جديد -->
            <div class="card mb-4 border border-primary">
                <div class="card-header bg-light border-0 py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold text-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('إضافة رصيد إجازة جديد') }}
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>{{ __('اختر نوع الإجازة') }}
                                <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative leave-type-dropdown-container">
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                        :value="selectedLeaveTypeId ? getLeaveTypeName(selectedLeaveTypeId) : leaveTypeSearch"
                                        @input="leaveTypeSearch = $event.target.value; selectedLeaveTypeId = ''; leaveTypeSearchOpen = true"
                                        @click="leaveTypeSearchOpen = true"
                                        @keydown.escape="leaveTypeSearchOpen = false"
                                        @keydown.arrow-down.prevent="navigateLeaveTypeDown()"
                                        @keydown.arrow-up.prevent="navigateLeaveTypeUp()"
                                        @keydown.enter.prevent="selectCurrentLeaveType()"
                                        :placeholder="selectedLeaveTypeId ? '' : '{{ __('ابحث عن نوع الإجازة...') }}'"
                                        autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button"
                                        @click="leaveTypeSearchOpen = !leaveTypeSearchOpen">
                                        <i class="fas" :class="leaveTypeSearchOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" type="button"
                                        x-show="selectedLeaveTypeId"
                                        @click="clearLeaveTypeSelection()"
                                        title="{{ __('مسح الاختيار') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Dropdown Results -->
                                <div x-show="leaveTypeSearchOpen && filteredLeaveTypes.length > 0"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 employee-dropdown"
                                    style="z-index: 999999 !important; max-height: 250px; overflow-y: auto; top: 100%; right: 0;"
                                    @click.away="leaveTypeSearchOpen = false"
                                    x-cloak>
                                    <template x-for="(leaveType, index) in filteredLeaveTypes" :key="leaveType.id">
                                        <div class="p-2 border-bottom cursor-pointer"
                                            @click="selectLeaveType(leaveType); leaveTypeSearchOpen = false"
                                            :class="leaveTypeSearchIndex === index ? 'bg-primary text-white' : 'hover-bg-light'">
                                            <div class="fw-bold" x-text="leaveType.name"></div>
                                            <small class="text-muted" x-show="leaveType.code" x-text="'الكود: ' + leaveType.code"></small>
                                        </div>
                                    </template>
                                </div>

                                <!-- No Results -->
                                <div x-show="leaveTypeSearchOpen && leaveTypeSearch && filteredLeaveTypes.length === 0"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 p-3 text-center text-muted employee-dropdown"
                                    style="z-index: 999999 !important; top: 100%; right: 0;"
                                    @click.away="leaveTypeSearchOpen = false"
                                    x-cloak>
                                    <i class="fas fa-search me-2"></i>
                                    <span x-text="leaveTypeSearch ? '{{ __('لا توجد نتائج') }}' : '{{ __('لا توجد أنواع إجازات متاحة') }}'"></span>
                                </div>
                            </div>
                            @error('selected_leave_type_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-main w-100"
                                @click="if(selectedLeaveTypeId) $wire.addLeaveBalance()"
                                :disabled="!selectedLeaveTypeId"
                                wire:loading.attr="disabled"
                                wire:target="addLeaveBalance">
                                <span wire:loading.remove wire:target="addLeaveBalance">
                                    <i class="fas fa-plus me-2"></i>{{ __('إضافة') }}
                                </span>
                                <span wire:loading wire:target="addLeaveBalance">
                                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('جاري الإضافة...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- أرصدة الإجازات المضافة -->
            <template x-if="leaveBalanceIds && leaveBalanceIds.length > 0">
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-list me-2 text-primary"></i>{{ __('أرصدة الإجازات المضافة') }}
                        <span class="badge bg-primary ms-2" x-text="leaveBalanceIds.length"></span>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center fw-bold">
                                        <i class="fas fa-calendar-check me-2"></i>{{ __('نوع الإجازة') }}
                                    </th>
                                    <th class="text-center fw-bold">
                                        <i class="fas fa-calendar me-2"></i>{{ __('السنة') }} <span class="text-danger">*</span>
                                    </th>
                                    <th class="text-center fw-bold">{{ __('الرصيد الافتتاحي') }}</th>
                                    <th class="text-center fw-bold">{{ __('المستخدمة') }}</th>
                                    <th class="text-center fw-bold">{{ __('المعلقة') }}</th>
                                    <th class="text-center fw-bold">{{ __('الحد الشهري الأقصى') }} <span class="text-danger">*</span></th>
                                    <th class="text-center fw-bold">{{ __('المتبقي') }}</th>
                                    <th class="text-center fw-bold">{{ __('ملاحظات') }}</th>
                                    <th class="text-center fw-bold">{{ __('إجراءات') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(balanceKey, index) in leaveBalanceIds" :key="balanceKey">
                                    <tr>
                                        <td class="align-middle">
                                            <span class="fw-bold text-primary">
                                                <i class="fas fa-calendar-check me-2"></i>
                                                <span x-text="getLeaveTypeName(leaveBalances[balanceKey].leave_type_id)"></span>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" class="form-control form-control-sm text-center"
                                                :value="leaveBalances[balanceKey].year || ''"
                                                @input="leaveBalances[balanceKey].year = parseInt($event.target.value) || ''"
                                                @keydown.enter.prevent
                                                min="2020" max="2030" placeholder="{{ now()->year }}">
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" class="form-control form-control-sm text-center"
                                                :value="leaveBalances[balanceKey].opening_balance_days || ''"
                                                @input="leaveBalances[balanceKey].opening_balance_days = parseFloat($event.target.value) || 0"
                                                @keydown.enter.prevent
                                                step="1" min="0" placeholder="0">
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" class="form-control form-control-sm text-center"
                                                :value="leaveBalances[balanceKey].used_days || ''"
                                                @input="leaveBalances[balanceKey].used_days = parseFloat($event.target.value) || 0"
                                                @keydown.enter.prevent
                                                step="1" min="0" placeholder="0">
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" class="form-control form-control-sm text-center"
                                                :value="leaveBalances[balanceKey].pending_days || ''"
                                                @input="leaveBalances[balanceKey].pending_days = parseFloat($event.target.value) || 0"
                                                @keydown.enter.prevent
                                                step="1" min="0" placeholder="0">
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" class="form-control form-control-sm text-center @error('leave_balances.*.max_monthly_days') is-invalid @enderror"
                                                :value="leaveBalances[balanceKey].max_monthly_days || ''"
                                                @input="leaveBalances[balanceKey].max_monthly_days = parseFloat($event.target.value) || 0"
                                                @keydown.enter.prevent
                                                step="0.5" min="0" 
                                                placeholder="0">
                                            @error('leave_balances.*.max_monthly_days')
                                                <small class="text-danger d-block mt-1">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge px-3 py-2 fw-bold"
                                                :class="calculateRemainingDays(leaveBalances[balanceKey]) >= 0 ? 'bg-success' : 'bg-danger'"
                                                x-text="calculateRemainingDays(leaveBalances[balanceKey])"></span>
                                        </td>
                                        <td class="align-middle">
                                            <textarea class="form-control form-control-sm" rows="1"
                                                :value="leaveBalances[balanceKey].notes || ''"
                                                @input="leaveBalances[balanceKey].notes = $event.target.value"
                                                placeholder="{{ __('أضف ملاحظات..') }}"></textarea>
                                        </td>
                                        <td class="align-middle text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                @click="$wire.removeLeaveBalance(balanceKey)" 
                                                title="{{ __('حذف رصيد الإجازة') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <!-- رسالة عند عدم وجود أرصدة -->
            <template x-if="!leaveBalanceIds || leaveBalanceIds.length === 0">
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <div class="mb-4">
                            <i class="fas fa-calendar-check fa-4x text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-bold mb-3">{{ __('لا توجد أرصدة إجازات') }}</h5>
                        <p class="text-muted mb-4">
                            {{ __('لم يتم إضافة أي رصيد إجازة بعد. استخدم النموذج أعلاه لإضافة رصيد إجازة.') }}
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

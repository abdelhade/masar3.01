<!-- Left Sidenav -->
<div class="left-sidenav">
    <div class="menu-content h-100" data-simplebar>
        <ul class="metismenu left-sidenav-menu">

            <li class="menu-label my-2"><a href="{{ route('home') }}">{{ config('public_settings.campany_name') }}</a>
            </li>


            <li class="nav-item border-bottom pb-1 mb-2">
                <a href="{{ route('home.index') }}"
                    class="nav-link d-flex align-items-center gap-2 font-family-cairo fw-bold">
                    <i data-feather="home" style="color:#4e73df" class="menu-icon"></i>
                    {{ __('navigation.home') }}
                </a>

            </li>
            <!-- البيانات الأساسية -->

            @canany([
                'عرض العملاء',
                'عرض الموردين',
                'عرض الصناديق',
                'عرض البنوك',
                'عرض الموظفين',
                'عرض المخازن',
                'عرض
                المصروفات',
                'عرض الايرادات',
                'عرض دائنين متنوعين',
                'عرض مدينين متنوعين',
                'عرض الشركاء',
                'عرض جارى الشركاء',
                'عرض الأصول الثابتة',
                'عرض الأصول القابلة للتأجير',
                ])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="database" style="color:#4e73df" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.master_data') }}</span>
                        <span class="menu-arrow">
                            <i class="mdi mdi-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض العملاء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'client']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.clients') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الموردين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'supplier']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.suppliers') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الصناديق')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'fund']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.funds') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض البنوك')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'bank']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.banks') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الموظفين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'employee']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.employees') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض المخازن')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'store']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.warehouses') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض المصروفات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'expense']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.expenses') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الايرادات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'revenue']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.revenues') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض دائنين متنوعين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'creditor']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.various_creditors') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض مدينين متنوعين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'depitor']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.various_debtors') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الشركاء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'partner']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.partners') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض جارى الشركاء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'current-partner']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.current_partners') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الأصول الثابتة')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'asset']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.fixed_assets') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الأصول القابلة للتأجير')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'rentable']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.rentable_assets') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['عرض الوحدات', 'عرض التصنيفات', 'عرض الأسعار', 'عرض الأصناف'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="box" style="color:#1cc88a" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.items') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض الوحدات')
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('units.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.units') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الأصناف')
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('items.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.items') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الأسعار')
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('prices.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.prices') }}
                                </a>
                            </li>
                        @endcan
                        @canany([
                            'عرض المقاسات',
                            'عرض الطباعه',
                            'عرض الاماكن',
                            'عرض المواقع',
                            'عرض التصنيفات',
                            'عرض
                            المجموعات',
                            ])
                            <livewire:item-management.notes.notesNames />
                        @endcan
                        <!-- {{-- item movement --}}
                                                    @can('عرض تقرير حركة صنف')
        <li class="nav-item">
                                                                                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('item-movement') }}">
                                                                                            <i class="ti-control-record"></i>{{ __('navigation.item_movement_report') }}
                                                                                        </a>
                                                                                    </li>
    @endcan
                                                    {{-- item movement --}} -->
                    </ul>
                </li>
            @endcanany

            @canany([
                'عرض قائمة الخصومات المسموح بها',
                'عرض قائمة الخصومات المكتسبة',
                'عرض خصم مسموح به',
                'عرض خصم
                مكتسب',
                ])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="percent" style="color:#f6c23e" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.discounts') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض قائمة الخصومات المسموح بها')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.index', ['type' => 30]) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.allowed_discounts') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض قائمة الخصومات المكتسبة')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.index', ['type' => 31]) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.earned_discounts') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض خصم مسموح به')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.create', ['type' => 30, 'q' => md5(30)]) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.allowed_discount') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض خصم مكتسب')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.create', ['type' => 31, 'q' => md5(31)]) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.earned_discount') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany

            @can('عرض فاتورة تصنيع')
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.manufacturing') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manufacturing.create') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.manufacturing_invoice') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @canany(['عرض الادوار', 'عرض المدراء'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.permissions') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض الادوار')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('roles.index', ['type' => 30]) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.roles') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض المدراء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('users.index', ['type' => 31]) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.managers') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['عرض العملااء', 'عرض مصدر الفرص', 'عرض جهات اتصال الشركات', 'عرض حالات الفرص', 'عرض الفرص'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.crm') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('statistics.index') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.statistics') }}
                            </a>
                        </li>
                        @can('عرض العملااء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('crm.clients.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.clients') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض مصدر الفرص')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('chance-sources.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.chance_sources') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض جهات اتصال الشركات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('client-contacts.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.client_contacts') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض حالات الفرص')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('lead-status.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.lead_statuses') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الفرص')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('leads.board') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.leads') }}

                                </a>
                            </li>
                        @endcan

                        @can('عرض الفرص')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('tasks.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.tasks') }}

                                </a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('activities.index') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.activities') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcanany

            @php
                $sections = [
                    'ادارة المبيعات' => [
                        10 => 'فاتورة مبيعات',
                        12 => 'مردود مبيعات',
                        14 => 'أمر بيع',
                        16 => 'عرض سعر لعميل',
                        22 => 'أمر حجز',
                    ],
                    'ادارة المشتريات' => [
                        11 => 'فاتورة مشتريات',
                        13 => 'مردود مشتريات',
                        15 => 'أمر شراء',
                        17 => 'عرض سعر من مورد',
                    ],
                    'ادارة المخزون' => [
                        18 => 'فاتورة تالف',
                        19 => 'أمر صرف',
                        20 => 'أمر إضافة',
                        21 => 'تحويل من مخزن لمخزن',
                    ],
                ];
            @endphp

            @foreach ($sections as $sectionTitle => $items)
                @php
                    $viewPermissions = [];
                    foreach ($items as $type => $label) {
                        $viewPermissions[] = 'عرض ' . $label;
                    }
                @endphp

                @canany($viewPermissions)
                    <li class="li-main">
                        <a href="javascript:void(0);">
                            <i data-feather="shopping-cart" style="color:#e74a3b"
                                class="align-self-center menu-icon"></i>
                            <span>{{ __($sectionTitle) }}</span>
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>

                        <ul class="sub-menu mm-collapse" aria-expanded="false">
                            @foreach ($items as $type => $label)
                                @can('عرض ' . $label)
                                    <li class="nav-item">
                                        <a class="nav-link"
                                            href="{{ url('/invoices/create?type=' . $type . '&q=' . md5($type)) }}">
                                            <i class="ti-control-record"></i> {{ __($label) }}

                                        </a>
                                    </li>
                                @endcan
                            @endforeach
                        </ul>
                    </li>
                @endcanany
            @endforeach

            @canany(['عرض احتساب الثابت للموظفين', 'عرض السندات', 'عرض سند دفع', 'عرض سند دفع متعدد', 'عرض سند قبض'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="file-text" style="color:#fd7e14" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.vouchers') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض سند قبض')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('vouchers.create', ['type' => 'receipt']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.general_receipt_voucher') }}
                                </a>
                            </li>
                        @endcan
                        @can(' سند دفع عامل')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('vouchers.create', ['type' => 'payment']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.general_payment_voucher') }}
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vouchers.create', ['type' => 'payment']) }}">
                                <i class="ti-control-record"></i>{{ __('navigation.general_payment_voucher') }}
                            </a>
                        </li>
                        @can('عرض السندات')
                            <li class="nav-item">

                                <a class="nav-link" href="{{ route('vouchers.create', ['type' => 'exp-payment']) }}">
                                    <i
                                        class="ti-control-record"></i>{{ __('navigation.general_payment_voucher_for_expenses') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض سند دفع متعدد')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'multi_payment']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.multi_payment_voucher') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض سند قبض متعدد')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'multi_receipt']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.multi_receipt_voucher') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany

            @can('عرض التحويلات النقدية')
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="repeat" style="color:#20c997" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.cash_transfers') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_cash']) }}">
                                <i class="ti-control-record"></i>{{ __('navigation.cash_to_cash_transfer') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_bank']) }}">
                                <i class="ti-control-record"></i>{{ __('navigation.cash_to_bank_transfer') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_cash']) }}">
                                <i class="ti-control-record"></i>{{ __('navigation.bank_to_cash_transfer') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_bank']) }}">
                                <i class="ti-control-record"></i>{{ __('navigation.bank_to_bank_transfer') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.index') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.cash_transfers') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @canany([
                'عرض احتساب الاضافي للموظفين',
                'عرض احتساب خصم للموظفين',
                'عرض احتساب تأمينات',
                'عرض احتساب ضريبة
                دخل',
                ])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="user-check" style="color:#17a2b8" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.employee_salaries') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض احتساب الثابت للموظفين')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'salary_calculation']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.fixed_salary_calculation') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض احتساب الاضافي للموظفين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'extra_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.extra_salary_calculation') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض احتساب خصم للموظفين')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'discount_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.discount_salary_calculation') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض احتساب تأمينات')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'insurance_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.insurance_calculation') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض احتساب ضريبة دخل')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'tax_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.tax_calculation') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany([
                'عرض سند قبض متعدد',
                'عرض اتفاقية خدمة',
                'عرض مصروفات مستحقة',
                'عرض ايرادات مستحقة',
                'عرض احتساب
                عمولة بنكية',
                'عرض عقد بيع',
                'عرض توزيع الارباح علي الشركا',
                ])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="clock" style="color:#6f42c1" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.accruals') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        @can('عرض اتفاقية خدمة')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'contract']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.service_agreement') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض مصروفات مستحقة')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'accured_expense']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.accured_expenses') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض ايرادات مستحقة')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'accured_income']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.accured_revenues') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض احتساب عمولة بنكية')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'bank_commission']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.bank_commission_calculation') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض عقد بيع')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'sales_contract']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.sales_contract') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض توزيع الارباح علي الشركا')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'partner_profit_sharing']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.partner_profit_sharing') }}
                                </a>
                            </li>
                        </ul>

                    </li>
                @endcan
            @endcanany

            @canany([
                'عرض اهلاك الاصل',
                'عرض بيع الاصول',
                'عرض شراء اصل',
                'عرض زيادة في قيمة الاصل',
                'عرض نقص في قيمة
                الاصل',
                ])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="hard-drive" style="color:#e83e8c" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.asset_operations') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض اهلاك الاصل')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.depreciation') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض بيع الاصول')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'sell_asset']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.sell_asset') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض شراء اصل')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'buy_asset']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.buy_asset') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض زيادة في قيمة الاصل')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'increase_asset_value']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.increase_asset_value') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض نقص في قيمة الاصل')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'decrease_asset_value']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.decrease_asset_value') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['عرض قيد يومية', 'عرض قيد يوميه متعدد', 'عرض قيود يومية عمليات', 'عرض قيود يوميه عمليات متعدده',
                'عرض قيود يوميه حسابات', 'عرض تسجيل الارصده الافتتاحيه للمخازن'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="bar-chart-2" style="color:#007bff" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.account_management') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض قيد يومية')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('journals.create', ['type' => 'basic_journal']) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.daily_journal') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض قيد يوميه متعدد')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-journals.create') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.multi_journal') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض قيود يومية عمليات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('journals.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.daily_ledgers_operations') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض قيود يوميه عمليات متعدده')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-journals.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.multi_daily_ledgers_operations') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض قيود يوميه حسابات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('journal-summery') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.daily_ledgers_accounts') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض تسجيل الارصده الافتتاحيه للمخازن')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('inventory-balance.create') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.opening_inventory_balance') }}
                                </a>
                            </li>
                        @endcan
                        {{-- الرصيد الافتتاحى للحسابات --}}
                        <li class="nav-item">
                            <a class="nav-link font-family-cairo fw-bold" href="{{ route('accounts.startBalance') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.opening_balance_accounts') }}
                            </a>
                        </li>
                        {{-- الرصيد الافتتاحى للحسابات --}}
                        {{-- account movement --}}
                        <!-- <li class="nav-item">
                                                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('account-movement') }}">
                                                            <i class="ti-control-record"></i>{{ __('navigation.account_movement_report') }}
                                                        </a>
                                                    </li> -->
                        {{-- account movement --}}
                        {{-- balance sheet --}}
                        <li class="nav-item">
                            <a class="nav-link font-family-cairo fw-bold" href="{{ route('accounts.balanceSheet') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.balance_sheet') }}
                            </a>
                        </li>
                        {{-- balance sheet --}}
                    </ul>
                </li>
            @endcanany

            @can('عرض المشاريع')
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="clipboard" style="color:#6610f2" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.projects') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        <li class="nav-item">
                            <a class="nav-link font-family-cairo fw-bold" href="{{ route('projects.index') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.projects') }}
                            </a>
                        </li>
                        <!-- rent -->
                        <li class="nav-item">
                            <a class="nav-link font-family-cairo fw-bold" href="{{ route('rentals.index') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.rentals') }}
                            </a>
                        </li>
                        <!-- rent -->
                    </ul>
                </li>
            @endcan

            @canany([
                'عرض الادارات و الاقسام',
                'عرض الوظائف',
                'عرض الدول',
                'عرض المحافظات',
                'عرض المدن',
                'عرض المناطق',
                'عرض الورديات',
                'عرض الموظفيين',
                'عرض المعدلات',
                'عرض تقييم الموظفين',
                'عرض انواع العقود',
                'عرض العقود',
                'عرض
                البصمات',
                'عرض معالجه الحضور والانصراف',
                ])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('navigation.human_resources') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض الادارات و الاقسام')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('departments.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.departments') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الوظائف')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('jobs.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.jobs') }}
                                </a>
                            </li>
                        @endcan
                        @canany(['عرض الدول', 'عرض المحافظات', 'عرض المدن', 'عرض المناطق'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('navigation.addresses') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض الدول')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('countries.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.countries') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض المحافظات')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('states.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.states') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض المدن')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('cities.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.cities') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض المناطق')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('towns.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.towns') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        @can('عرض الورديات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shifts.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.shifts') }}
                                </a>
                            </li>
                        @endcan
                        @can('عرض الموظفيين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('employees.index') }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.employees') }}
                                </a>
                            </li>
                        @endcan
                        @canany(['عرض المعدلات', 'عرض تقييم الموظفين'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('navigation.performance_kpis') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض المعدلات')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('kpis.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.kpis') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can(abilities: 'عرض معدلات اداء الموظفين')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('kpis.employeeEvaluation') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.employee_performance_kpis') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        @canany(['عرض انواع العقود', 'عرض العقود'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('navigation.contracts') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض انواع العقود')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('contract-types.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.contract_types') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض العقود')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('contracts.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.contracts') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        @canany(['عرض البصمات', 'عرض معالجه الحضور والانصراف'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('navigation.attendance') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض البصمات')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('attendances.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.attendance_records') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض معالجه الحضور والانصرف')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('attendance.processing') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.attendance_processing') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        {{-- إدارة الإجازات --}}
                        @canany(['عرض رصيد الإجازات', 'عرض طلبات الإجازة'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('navigation.leave_management') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض رصيد الإجازات')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('leaves.balances.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.leave_balances') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض طلبات الإجازة')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('leaves.requests.index') }}">
                                                <i class="ti-control-record"></i>{{ __('navigation.leave_requests') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        {{-- CVs --}}
                        <li class="nav-item">
                            <a class="nav-link font-family-cairo fw-bold" href="{{ route('cvs.index') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.cv_management') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcanany
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="settings" class="align-self-center menu-icon"></i>
                    <span>{{ __('navigation.settings') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('barcode.print.settings.edit') }}">
                            <i class="ti-control-record"></i>{{ __('navigation.barcode_settings') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('export-settings') }}">
                            <i class="ti-control-record"></i>{{ __('navigation.data_backup') }}
                        </a>
                    </li>
                </ul>
            </li>

            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="settings" class="align-self-center menu-icon"></i>
                    <span>{{ __('أدارة المستأجرات') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rentals.buildings.index') }}">
                            <i class="ti-control-record"></i>{{ __('أستأجار مبني') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rentals.leases.index') }}">
                            <i class="ti-control-record"></i>{{ __('عقود الاستأجار') }}
                        </a>
                    </li>

                </ul>
            </li>

            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="settings" class="align-self-center menu-icon"></i>
                    <span>{{ __('الصيانه') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('service.types.index') }}">
                            <i class="ti-control-record"></i>{{ __('انواع الخدمات') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('maintenances.index') }}">
                            <i class="ti-control-record"></i>{{ __('الصيانات') }}
                        </a>
                    </li>

                </ul>
            </li>

            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="truck" class="align-self-center menu-icon"></i>
                    <span>{{ __('navigation.shipping_management') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('companies.index') }}">
                            <i class="ti-control-record"></i>{{ __('navigation.shipping_companies') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('drivers.index') }}">
                            <i class="ti-control-record"></i>{{ __('navigation.drivers') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('orders.index') }}">
                            <i class="ti-control-record"></i>{{ __('navigation.orders') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('shipments.index') }}">
                            <i class="ti-control-record"></i>{{ __('navigation.shipments') }}
                        </a>
                    </li>
                </ul>
            </li>

            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="truck" class="align-self-center menu-icon"></i>
                    <span>{{ __('Daily Progress') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('project.types.index') }}">
                            <i class="ti-control-record"></i>{{ __('Project Types') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('clients.index') }}">
                            <i class="ti-control-record"></i>{{ __('Clients') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employees.index') }}">
                            <i class="ti-control-record"></i>{{ __('Employees') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('work.items.index') }}">
                            <i class="ti-control-record"></i>{{ __('Work Items') }}
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</div>

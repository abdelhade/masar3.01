<!-- Left Sidenav -->
<div class="left-sidenav">
    <div class="menu-content h-100" data-simplebar>
        <ul class="metismenu left-sidenav-menu">

            <li class="menu-label my-2"><a href="{{ route('home') }}">{{ __('MASAR FOR TECNOLOGY') }}</a></li>

            <li class="nav-item border-bottom pb-1 mb-2">
                <a href="{{ route('home.index') }}"
                    class="nav-link d-flex align-items-center gap-2 font-family-cairo fw-bold">
                    <i data-feather="home" style="color:#4e73df" class="menu-icon"></i>
                    {{ __('الرئيسيه') }}
                </a>
            </li>
            <!-- البيانات الأساسية -->

            @canany(['عرض العملاء', 'عرض الموردين', 'عرض الصناديق', 'عرض البنوك', 'عرض الموظفين', 'عرض المخازن', 'عرض
                المصروفات', 'عرض الايرادات', 'عرض دائنين متنوعين', 'عرض مدينين متنوعين', 'عرض الشركاء', 'عرض جارى الشركاء',
                'عرض الأصول الثابتة', 'عرض الأصول القابلة للتأجير'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="database" style="color:#4e73df" class="align-self-center menu-icon"></i>
                        <span>{{ __('البيانات الأساسية') }}</span>
                        <span class="menu-arrow">
                            <i class="mdi mdi-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        @can('عرض العملاء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'client']) }}">
                                    <i class="ti-control-record"></i>{{ __('العملاء') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الموردين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'supplier']) }}">
                                    <i class="ti-control-record"></i>{{ __('الموردين') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الصناديق')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'fund']) }}">
                                    <i class="ti-control-record"></i>{{ __('الصناديق') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض البنوك')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'bank']) }}">
                                    <i class="ti-control-record"></i>{{ __('البنوك') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الموظفين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'employee']) }}">
                                    <i class="ti-control-record"></i>{{ __('الموظفين') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض المخازن')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'store']) }}">
                                    <i class="ti-control-record"></i>{{ __('المخازن') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض المصروفات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'expense']) }}">
                                    <i class="ti-control-record"></i>{{ __('المصروفات') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الايرادات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'revenue']) }}">
                                    <i class="ti-control-record"></i>{{ __('الايرادات') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض دائنين متنوعين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'creditor']) }}">
                                    <i class="ti-control-record"></i>{{ __('دائنين متنوعين') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض مدينين متنوعين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'depitor']) }}">
                                    <i class="ti-control-record"></i>{{ __('مدينين متنوعين') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الشركاء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'partner']) }}">
                                    <i class="ti-control-record"></i>{{ __('الشركاء') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض جارى الشركاء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'current-partner']) }}">
                                    <i class="ti-control-record"></i>{{ __(' جارى الشركاء') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الأصول الثابتة')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'asset']) }}">
                                    <i class="ti-control-record"></i>{{ __('الأصول الثابتة') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الأصول القابلة للتأجير')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('accounts.index', ['type' => 'rentable']) }}">
                                    <i class="ti-control-record"></i>{{ __('الأصول القابلة للتأجير') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany


            <!-- الأصناف-->
            @canany(['عرض الوحدات', 'عرض التصنيفات', 'عرض الأسعار', 'عرض الأصناف'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="box" style="color:#1cc88a" class="align-self-center menu-icon"></i>
                        <span>{{ __('الأصناف') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="database" style="color:#4e73df" class="align-self-center menu-icon"></i>
                    <span>{{ __('البيانات الأساسية') }}</span>
                    <span class="menu-arrow">
                        <i class="mdi mdi-chevron-right"></i>
                    </span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'client']) }}">
                            <i class="ti-control-record"></i>{{ __('العملاء') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'supplier']) }}">
                            <i class="ti-control-record"></i>{{ __('الموردين') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'fund']) }}">
                            <i class="ti-control-record"></i>{{ __('الصناديق') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'bank']) }}">
                            <i class="ti-control-record"></i>{{ __('البنوك') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'employee']) }}">
                            <i class="ti-control-record"></i>{{ __('الموظفين') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'store']) }}">
                            <i class="ti-control-record"></i>{{ __('المخازن') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'expense']) }}">
                            <i class="ti-control-record"></i>{{ __('المصروفات') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'revenue']) }}">
                            <i class="ti-control-record"></i>{{ __('الايرادات') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'creditor']) }}">
                            <i class="ti-control-record"></i>{{ __('دائنين متنوعين') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'depitor']) }}">
                            <i class="ti-control-record"></i>{{ __('مدينين متنوعين') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'partner']) }}">
                            <i class="ti-control-record"></i>{{ __('الشركاء') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'current-partner']) }}">
                            <i class="ti-control-record"></i>{{ __(' جارى الشركاء') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'asset']) }}">
                            <i class="ti-control-record"></i>{{ __('الأصول الثابتة') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('accounts.index', ['type' => 'rentable']) }}">
                            <i class="ti-control-record"></i>{{ __('الأصول القابلة للتأجير') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- الأصناف -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="box" style="color:#1cc88a" class="align-self-center menu-icon"></i>
                    <span>{{ __('الأصناف') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('units.index') }}">
                            <i class="ti-control-record"></i>{{ __('الوحدات') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('items.index') }}">
                            <i class="ti-control-record"></i>{{ __('الأصناف') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('prices.index') }}">
                            <i class="ti-control-record"></i>{{ __('الأسعار') }}
                        </a>
                    </li>


                        @can('عرض الوحدات')
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('units.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الوحدات') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض التصنيفات')
                            <livewire:item-management.notes.notesNames />
                        @endcan


                        @can('عرض الأسعار')
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('prices.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الأسعار') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الأصناف')
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('items.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الأصناف') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany


            {{-- الخصومات --}}
            @canany(['عرض قائمة الخصومات المسموح بها', 'عرض قائمة الخصومات المكتسبة', 'عرض خصم مسموح به', 'عرض خصم
                مكتسب'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="percent" style="color:#f6c23e" class="align-self-center menu-icon"></i>
                        <span>{{ __('الخصومات') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        @can('عرض قائمة الخصومات المسموح بها')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.index', ['type' => 30]) }}">
                                    <i class="ti-control-record"></i>{{ __('قائمة الخصومات المسموح بها') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض قائمة الخصومات المكتسبة')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.index', ['type' => 31]) }}">
                                    <i class="ti-control-record"></i>{{ __('قائمة الخصومات المكتسبة') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض خصم مسموح به')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.create', ['type' => 30, 'q' => md5(30)]) }}">
                                    <i class="ti-control-record"></i>{{ __('خصم مسموح به') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض خصم مكتسب')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('discounts.create', ['type' => 31, 'q' => md5(31)]) }}">
                                    <i class="ti-control-record"></i>{{ __('خصم مكتسب') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany


            {{-- التصنيع --}}
            @can('عرض فاتورة تصنيع')
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('التصنيع') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manufacturing.create') }}">
                                <i class="ti-control-record"></i>{{ __('فاتورة تصنيع') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan


            @canany(['عرض الادوار', 'عرض المدراء'])
                {{-- الصلاحيات --}}
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('الصلاحيات') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        @can('عرض الادوار')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('roles.index', ['type' => 30]) }}">
                                    <i class="ti-control-record"></i>{{ __('الادوار') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض المدراء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('users.index', ['type' => 31]) }}">
                                    <i class="ti-control-record"></i>{{ __('المدراء') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany


            @canany(['عرض العملاء', 'عرض مصدر الفرص', 'عرض جهات اتصال الشركات', 'عرض حالات الفرص', 'عرض الفرص'])
                {{-- CRM --}}
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('CRM') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        @can('عرض العملاء')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('clients.index') }}">
                                    <i class="ti-control-record"></i>{{ __('العملاء') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض مصدر الفرص')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('chance-sources.index') }}">
                                    <i class="ti-control-record"></i>{{ __('مصدر الفرص') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض جهات اتصال الشركات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('client-contacts.index') }}">
                                    <i class="ti-control-record"></i>{{ __('جهات اتصال الشركات') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض حالات الفرص')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('lead-status.index') }}">
                                    <i class="ti-control-record"></i>{{ __('حالات الفرص') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الفرص')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('leads.board') }}">
                                    <i class="ti-control-record"></i>{{ __('الفرص') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany


            {{-- اداره المبيعات و المشتريات و المخزون --}}

                    <livewire:item-management.notes.notesNames />
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('item-movement') }}">
                            <i class="ti-control-record"></i>{{ __('حركه الصنف') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- الخصومات -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="percent" style="color:#f6c23e" class="align-self-center menu-icon"></i>
                    <span>{{ __('الخصومات') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('discounts.index', ['type' => 30]) }}">
                            <i class="ti-control-record"></i>{{ __('قائمة الخصومات المسموح بها') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('discounts.index', ['type' => 31]) }}">
                            <i class="ti-control-record"></i>{{ __('قائمة الخصومات المكتسبة') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('discounts.create', ['type' => 30, 'q' => md5(30)]) }}">
                            <i class="ti-control-record"></i>{{ __('خصم مسموح به') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('discounts.create', ['type' => 31, 'q' => md5(31)]) }}">
                            <i class="ti-control-record"></i>{{ __('خصم مكتسب') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- التصنيع -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('manufacturing.create') }}">
                    <i data-feather="tool" style="color:#36b9cc" class="align-self-center menu-icon"></i>
                    <span>{{ __('التصنيع') }}</span>
                </a>
            </li>

            <!-- الصلاحيات -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="lock" style="color:#e74a3b" class="align-self-center menu-icon"></i>
                    <span>{{ __('الصلاحيات') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('roles.index', ['type' => 30]) }}">
                            <i class="ti-control-record"></i>{{ __('الادوار') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('users.index', ['type' => 31]) }}">
                            <i class="ti-control-record"></i>{{ __('المدراء') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- CRM -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="users" style="color:#4e73df" class="align-self-center menu-icon"></i>
                    <span>{{ __('CRM') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('clients.index') }}">
                            <i class="ti-control-record"></i>{{ __('العملاء') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('chance-sources.index') }}">
                            <i class="ti-control-record"></i>{{ __('مصدر الفرص ') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('client-contacts.index') }}">
                            <i class="ti-control-record"></i>{{ __('جهات اتصال الشركات ') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('lead-status.index') }}">
                            <i class="ti-control-record"></i>{{ __('حالات الفرص ') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('leads.board') }}">
                            <i class="ti-control-record"></i>{{ __(' الفرص ') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- أقسام الفواتير -->

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
                    $permissions = [];
                    foreach ($items as $type => $label) {
                        $permissions[] = 'عرض ' . $label;
                    }
                @endphp

                @canany($permissions)
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
                                            <i class="ti-control-record"></i>
                                            {{ __($label) }}
                                        </a>
                                    </li>
                                @endcan
                            @endforeach
                        </ul>
                    </li>
                @endcanany
            @endforeach


            @can('عرض السندات')
                <!-- السندات -->
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="file-text" style="color:#fd7e14" class="align-self-center menu-icon"></i>
                        <span>{{ __('السندات') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vouchers.create', ['type' => 'receipt']) }}">
                                <i class="ti-control-record"></i>{{ __('سند قبض') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vouchers.create', ['type' => 'payment']) }}">
                                <i class="ti-control-record"></i>{{ __('سند دفع') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vouchers.index') }}">
                                <i class="ti-control-record"></i>{{ __('السندات') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'multi_payment']) }}">
                                <i class="ti-control-record"></i>{{ __('سند دفع متعدد') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('multi-vouchers.create', ['type' => 'salary_calculation']) }}">
                                <i class="ti-control-record"></i>{{ __('احتساب الثابت للموظفين') }}
                            </a>
                        </li>

                    </ul>
                </li>
            @endcan

            <!-- التحويلات النقدية -->
            @can('عرض التحويلات النقدية')
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="repeat" style="color:#20c997" class="align-self-center menu-icon"></i>
                        <span>{{ __('التحويلات النقدية') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_cash']) }}">
                                <i class="ti-control-record"></i>{{ __('تحويل نقدية من صندوق لصندوق') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_bank']) }}">
                                <i class="ti-control-record"></i>{{ __('تحويل نقدية من صندوق لبنك') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_cash']) }}">
                                <i class="ti-control-record"></i>{{ __('تحويل من بنك لصندوق') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_bank']) }}">
                                <i class="ti-control-record"></i>{{ __('تحويل من بنك لبنك') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfers.index') }}">
                                <i class="ti-control-record"></i>{{ __('التحويلات النقدية') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            <!-- رواتب الموظفين  -->
            @canany(['عرض احتساب الاضافي للموظفين', 'عرض احتساب خصم للموظفين', 'عرض احتساب تأمينات', 'عرض احتساب ضريبة
                دخل'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="user-check" style="color:#17a2b8" class="align-self-center menu-icon"></i>
                        <span>{{ __('رواتب الموظفين') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض احتساب الاضافي للموظفين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'extra_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('احتساب الاضافي للموظفين') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض احتساب خصم للموظفين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'discount_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('احتساب خصم للموظفين') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض احتساب تأمينات')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'insurance_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('احتساب تأمينات') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض احتساب ضريبة دخل')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'tax_calc']) }}">
                                    <i class="ti-control-record"></i>{{ __('احتساب ضريبة دخل') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany



            {{-- الاستحقاقات --}}
            @canany(['عرض سند قبض متعدد', 'عرض اتفاقية خدمة', 'عرض مصروفات مستحقة', 'عرض ايرادات مستحقة', 'عرض احتساب
                عمولة بنكية', 'عرض عقد بيع', 'عرض توزيع الارباح علي الشركا'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="clock" style="color:#6f42c1" class="align-self-center menu-icon"></i>
                        <span>{{ __('الاستحقاقات') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض سند قبض متعدد')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'multi_receipt']) }}">
                                    <i class="ti-control-record"></i>{{ __('سند قبض متعدد') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض اتفاقية خدمة')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'contract']) }}">
                                    <i class="ti-control-record"></i>{{ __('اتفاقية خدمة') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض مصروفات مستحقة')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'accured_expense']) }}">
                                    <i class="ti-control-record"></i>{{ __('مصروفات مستحقة') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض ايرادات مستحقة')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'accured_income']) }}">
                                    <i class="ti-control-record"></i>{{ __('ايرادات مستحقة') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض احتساب عمولة بنكية')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'bank_commission']) }}">
                                    <i class="ti-control-record"></i>{{ __('احتساب عمولة بنكية') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض عقد بيع')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'sales_contract']) }}">
                                    <i class="ti-control-record"></i>{{ __('عقد بيع') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض توزيع الارباح علي الشركا')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'partner_profit_sharing']) }}">
                                    <i class="ti-control-record"></i>{{ __('توزيع الارباح علي الشركاء') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany



            <!-- عمليات الاصول -->
            @canany(['عرض اهلاك الاصل', 'عرض بيع الاصول', 'عرض شراء اصل', 'عرض زيادة في قيمة الاصل', 'عرض نقص في قيمة
                الاصل'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="hard-drive" style="color:#e83e8c" class="align-self-center menu-icon"></i>
                        <span>{{ __('عمليات الاصول') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        @can('عرض اهلاك الاصل')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
                                    <i class="ti-control-record"></i>{{ __('اهلاك الاصل') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض بيع الاصول')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'sell_asset']) }}">
                                    <i class="ti-control-record"></i>{{ __('بيع الاصول') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض شراء اصل')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'buy_asset']) }}">
                                    <i class="ti-control-record"></i>{{ __('شراء اصل') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض زيادة في قيمة الاصل')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'increase_asset_value']) }}">
                                    <i class="ti-control-record"></i>{{ __('زيادة في قيمة الاصل') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض نقص في قيمة الاصل')
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('multi-vouchers.create', ['type' => 'decrease_asset_value']) }}">
                                    <i class="ti-control-record"></i>{{ __('نقص في قيمة الاصل') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany

            {{-- ادارة الحسابات --}}
            @canany(['عرض قيد يومية', 'عرض قيد يوميه متعدد', 'عرض قيود يومية عمليات', 'عرض قيود يوميه عمليات متعدده',
                'عرض قيود يوميه حسابات', 'عرض تسجيل الارصده الافتتاحيه للمخازن'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="bar-chart-2" style="color:#007bff" class="align-self-center menu-icon"></i>
                        <span>{{ __('ادارة الحسابات') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">

                        @can('عرض قيد يومية')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('journals.create', ['type' => 'basic_journal']) }}">
                                    <i class="ti-control-record"></i>{{ __('قيد يومية') }}
                        @foreach ($items as $type => $label)
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold"
                                    href="{{ route('invoices.create', ['type' => $type, 'q' => md5($type)]) }}">
                                    <i class="ti-control-record"></i>
                                    {{ __($label) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach

            <!-- السندات -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="file-text" style="color:#fd7e14" class="align-self-center menu-icon"></i>
                    <span>{{ __('السندات') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('vouchers.create', ['type' => 'receipt']) }}">
                            <i class="ti-control-record"></i>{{ __('سند قبض') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('vouchers.create', ['type' => 'payment']) }}">
                            <i class="ti-control-record"></i>{{ __('سند دفع') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('vouchers.index') }}">
                            <i class="ti-control-record"></i>{{ __('السندات') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'multi_payment']) }}">
                            <i class="ti-control-record"></i>{{ __('سند دفع متعدد') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'salary_calculation']) }}">
                            <i class="ti-control-record"></i>{{ __('احتساب الثابت للموظفين') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- التحويلات النقدية -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="repeat" style="color:#20c997" class="align-self-center menu-icon"></i>
                    <span>{{ __('التحويلات النقدية') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('transfers.create', ['type' => 'cash_to_cash']) }}">
                            <i class="ti-control-record"></i>{{ __('تحويل نقدية من صندوق لصندوق') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('transfers.create', ['type' => 'cash_to_bank']) }}">
                            <i class="ti-control-record"></i>{{ __('تحويل نقدية من صندوق لبنك') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('transfers.create', ['type' => 'bank_to_cash']) }}">
                            <i class="ti-control-record"></i>{{ __('تحويل من بنك لصندوق') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('transfers.create', ['type' => 'bank_to_bank']) }}">
                            <i class="ti-control-record"></i>{{ __('تحويل من بنك لبنك') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('transfers.index') }}">
                            <i class="ti-control-record"></i>{{ __('التحويلات النقدية') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- رواتب الموظفين -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="user-check" style="color:#17a2b8" class="align-self-center menu-icon"></i>
                    <span>{{ __('رواتب الموظفين') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'extra_calc']) }}">
                            <i class="ti-control-record"></i>{{ __('احتساب الاضافي للموظفين') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'discount_calc']) }}">
                            <i class="ti-control-record"></i>{{ __('احتساب خصم للموظفين') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'insurance_calc']) }}">
                            <i class="ti-control-record"></i>{{ __('احتساب تأمينات') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'tax_calc']) }}">
                            <i class="ti-control-record"></i>{{ __('احتساب ضريبة دخل') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- الاستحقاقات -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="clock" style="color:#6f42c1" class="align-self-center menu-icon"></i>
                    <span>{{ __('الاستحقاقات') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'multi_receipt']) }}">
                            <i class="ti-control-record"></i>{{ __('سند قبض متعدد') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'contract']) }}">
                            <i class="ti-control-record"></i>{{ __('اتفاقية خدمة') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'accured_expense']) }}">
                            <i class="ti-control-record"></i>{{ __('مصروفات مستحقة') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'accured_income']) }}">
                            <i class="ti-control-record"></i>{{ __('ايرادات مستحقة') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'bank_commission']) }}">
                            <i class="ti-control-record"></i>{{ __('احتساب عمولة بنكية') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'sales_contract']) }}">
                            <i class="ti-control-record"></i>{{ __('عقد بيع') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'partner_profit_sharing']) }}">
                            <i class="ti-control-record"></i>{{ __('توزيع الارباح علي الشركاء') }}
                        </a>
                    </li>
                </ul>
            </li>



            <!-- عمليات الاصول  -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="grid" class="align-self-center menu-icon"></i>

            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="hard-drive" style="color:#e83e8c" class="align-self-center menu-icon"></i>
                    <span>{{ __('عمليات الاصول') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
                            <i class="ti-control-record"></i>{{ __(' اهلاك الاصل') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'sell_asset']) }}">
                            <i class="ti-control-record"></i>{{ __('بيع الاصول') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'buy_asset']) }}">
                            <i class="ti-control-record"></i>{{ __('شراء اصل') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'increase_asset_value']) }}">
                            <i class="ti-control-record"></i>{{ __('زيادة في قيمة الاصل') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('multi-vouchers.create', ['type' => 'decrease_asset_value']) }}">
                            <i class="ti-control-record"></i>{{ __('نقص في قيمة الاصل') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ادارة الحسابات -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="bar-chart-2" style="color:#007bff" class="align-self-center menu-icon"></i>
                    <span>{{ __('ادارة الحسابات') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold"
                            href="{{ route('journals.create', ['type' => 'basic_journal']) }}">
                            <i class="ti-control-record"></i>{{ __('قيد يومية') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('multi-journals.create') }}">
                            <i class="ti-control-record"></i>{{ __('قيد يومية متعدد') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('journals.index') }}">
                            <i class="ti-control-record"></i>{{ __('قيود اليومية _عمليات_') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('multi-journals.index') }}">
                            <i class="ti-control-record"></i>{{ __('قيود اليومية المتعددة _عمليات_') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('journal-summery') }}">
                            <i class="ti-control-record"></i>{{ __('قيود اليومية - حسابات') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('inventory-balance.create') }}">
                            <i class="ti-control-record"></i>{{ __('تسجيل الارصده الافتتاحيه للمخازن') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('accounts.startBalance') }}">
                            <i class="ti-control-record"></i>{{ __(' تسجيل الارصده الافتتاحيه للحسابات') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('account-movement') }}">
                            <i class="ti-control-record"></i>{{ __('تقرير حركه حساب') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- المشاريع -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="clipboard" style="color:#6610f2" class="align-self-center menu-icon"></i>
                    <span>{{ __('إدارة المشاريع') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('projects.index') }}">
                            <i class="ti-control-record"></i>{{ __('المشاريع') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- الموارد البشرية -->
            <li class="li-main">
                <a href="javascript: void(0);">
                    <i data-feather="users" style="color:#6f42c1" class="align-self-center menu-icon"></i>
                    <span>{{ __('الموارد البشرية') }}</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="sub-menu mm-collapse" aria-expanded="false">
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('departments.index') }}">
                            <i class="ti-control-record"></i>{{ __('الإدارات والأقسام') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('jobs.index') }}">
                            <i class="ti-control-record"></i>{{ __('الوظائف') }}
                        </a>
                    </li>
                    <li class="nav-item has-submenu">
                        <a class="nav-link font-family-cairo fw-bold" href="javascript: void(0);">
                            <i class="ti-control-record"></i>{{ __('العناوين') }}
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="sub-menu mm-collapse" aria-expanded="false">
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('countries.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الدول') }}

                                </a>
                            </li>
                        @endcan

                        @can('عرض قيد يوميه متعدد')
                            <li class="nav-item">

                                <a class="nav-link" href="{{ route('multi-journals.create') }}">
                                    <i class="ti-control-record"></i>{{ __('قيد يومية متعدد') }}

                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('states.index') }}">
                                    <i class="ti-control-record"></i>{{ __('المحافظات') }}

                                </a>
                            </li>
                        @endcan

                        @can('عرض قيود يومية عمليات')
                            <li class="nav-item">

                                <a class="nav-link" href="{{ route('journals.index') }}">
                                    <i class="ti-control-record"></i>{{ __('قيود اليومية _عمليات_') }}

                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('cities.index') }}">
                                    <i class="ti-control-record"></i>{{ __('المدن') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض قيود يوميه عمليات متعدده')
                            <li class="nav-item">

                                <a class="nav-link" href="{{ route('multi-journals.index') }}">
                                    <i class="ti-control-record"></i>{{ __('قيود اليومية المتعددة _عمليات_') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض قيود يوميه حسابات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('journal-summery') }}">
                                    <i class="ti-control-record"></i>{{ __('قيود اليومية - حسابات') }}

                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('towns.index') }}">
                                    <i class="ti-control-record"></i>{{ __('المناطق') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('shifts.index') }}">
                            <i class="ti-control-record"></i>{{ __('الورديات') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-family-cairo fw-bold" href="{{ route('employees.index') }}">
                            <i class="ti-control-record"></i>{{ __('الموظفين') }}
                        </a>
                    </li>
                    <li class="nav-item has-submenu">
                        <a class="nav-link font-family-cairo fw-bold" href="javascript: void(0);">
                            <i class="ti-control-record"></i>{{ __('معدلات الأداء') }}
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="sub-menu mm-collapse" aria-expanded="false">
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('kpis.index') }}">
                                    <i class="ti-control-record"></i>{{ __('المعدلات') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض تسجيل الارصده الافتتاحيه للمخازن')
                            <li class="nav-item">

                                <a class="nav-link" href="{{ route('inventory-balance.index') }}">
                                    <i class="ti-control-record"></i>{{ __('تسجيل الارصده الافتتاحيه للمخازن') }}
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany


            @can('عرض المشاريع')
                {{-- المشاريع --}}
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('إدارة المشاريع') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        <li class="nav-item">
                            <a class="nav-link font-family-cairo fw-bold" href="{{ route('projects.index') }}">
                                <i class="ti-control-record"></i>{{ __('المشاريع') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            {{-- الموارد البشرية --}}
            @canany(['عرض الادارات و الاقسام', 'عرض الوظائف', 'عرض الدول', 'عرض المحافظات', 'عرض المدن', 'عرض المناطق',
                'عرض الورديات', 'عرض الموظفين', 'عرض المعدلات', 'عرض تقييم الموظفين', 'عرض انواع العقود', 'عرض العقود', 'عرض
                البصمات', 'عرض معالجه الحضور والانصراف'])
                <li class="li-main">
                    <a href="javascript: void(0);">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>{{ __('الموارد البشرية') }}</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        @can('عرض الادارات و الاقسام')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('departments.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الإدارات والأقسام') }}

                                <a class="nav-link font-family-cairo fw-bold"
                                    href="{{ route('kpis.employeeEvaluation') }}">
                                    <i class="ti-control-record"></i>{{ __('معدلات أداء الموظفين') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- العقود -->
                    <li class="nav-item has-submenu">
                        <a class="nav-link font-family-cairo fw-bold" href="javascript: void(0);">
                            <i class="ti-control-record"></i>{{ __('العقود') }}
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="sub-menu mm-collapse" aria-expanded="false">
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold"
                                    href="{{ route('contract-types.index') }}">
                                    <i class="ti-control-record"></i>{{ __('أنواع العقود') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الوظائف')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('jobs.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الوظائف') }}
                                </a>
                            </li>
                        @endcan

                        @canany(['عرض الدول', 'عرض المحافظات', 'عرض المدن', 'عرض المناطق'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('العناوين') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض الدول')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('countries.index') }}">
                                                <i class="ti-control-record"></i>{{ __('الدول') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض المحافظات')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('states.index') }}">
                                                <i class="ti-control-record"></i>{{ __('المحافظات') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض المدن')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('cities.index') }}">
                                                <i class="ti-control-record"></i>{{ __('المدن') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض المناطق')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('towns.index') }}">
                                                <i class="ti-control-record"></i>{{ __('المناطق') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany

                        @can('عرض الورديات')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shifts.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الورديات') }}

                                <a class="nav-link font-family-cairo fw-bold" href="{{ route('contracts.index') }}">
                                    <i class="ti-control-record"></i>{{ __('العقود') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- الحضور والانصراف -->
                    <li class="nav-item has-submenu">
                        <a class="nav-link font-family-cairo fw-bold" href="javascript: void(0);">
                            <i class="ti-control-record"></i>{{ __('الحضور والانصراف') }}
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="sub-menu mm-collapse" aria-expanded="false">
                            <li class="nav-item">
                                <a class="nav-link font-family-cairo fw-bold"
                                    href="{{ route('attendances.index') }}">
                                    <i class="ti-control-record"></i>{{ __('البصمات') }}
                                </a>
                            </li>
                        @endcan

                        @can('عرض الموظفين')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('employees.index') }}">
                                    <i class="ti-control-record"></i>{{ __('الموظفين') }}
                                </a>
                            </li>
                        @endcan

                        @canany(['عرض المعدلات', 'عرض تقييم الموظفين'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('معدلات الأداء') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض المعدلات')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('kpis.index') }}">
                                                <i class="ti-control-record"></i>{{ __('المعدلات') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض تقييم الموظفين')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('kpis.employeeEvaluation') }}">
                                                <i class="ti-control-record"></i>{{ __('معدلات أداء الموظفين') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany

                        @canany(['عرض انواع العقود', 'عرض العقود'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('العقود') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض انواع العقود')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('contract-types.index') }}">
                                                <i class="ti-control-record"></i>{{ __('أنواع العقود') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض العقود')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('contracts.index') }}">
                                                <i class="ti-control-record"></i>{{ __('العقود') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany

                        @canany(['عرض البصمات', 'عرض معالجه الحضور والانصراف'])
                            <li class="nav-item has-submenu">
                                <a class="nav-link" href="javascript: void(0);">
                                    <i class="ti-control-record"></i>{{ __('الحضور والانصراف') }}
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="sub-menu mm-collapse">
                                    @can('عرض البصمات')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('attendances.index') }}">
                                                <i class="ti-control-record"></i>{{ __('البصمات') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('عرض معالجه الحضور والانصراف')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('attendance-processing.index') }}">
                                                <i class="ti-control-record"></i>{{ __('معالجة الحضور والانصراف') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                    </ul>
                </li>
            @endcanany


                                <a class="nav-link font-family-cairo fw-bold"
                                    href="{{ route('attendance-processing.index') }}">
                                    <i class="ti-control-record"></i>{{ __('معالجة الحضور والانصراف') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>

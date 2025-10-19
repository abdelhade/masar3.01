{{-- @php
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
            19 => ' فواتير أمر صرف',
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
                <i data-feather="shopping-cart" style="color:#e74a3b" class="align-self-center menu-icon"></i>
                <span>{{ __($sectionTitle) }}</span>
                <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
            </a>

            <ul class="sub-menu mm-collapse" aria-expanded="false">
                @foreach ($items as $type => $label)
                    @can('عرض ' . $label)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                                <i class="ti-control-record"></i> {{ __($label) }}
                            </a>
                        </li>
                    @endcan
                @endforeach
            </ul>
        </li>
    @endcanany
@endforeach --}}

@extends('admin.dashboard')

@section('sidebar')
    @if (in_array($type, [10, 12, 14, 16, 22, 26]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($type, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($type, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection

@section('content')
    @php
        $titles = [
            10 => 'فاتورة مبيعات',
            11 => 'فاتورة مشتريات',
            12 => 'مردود مبيعات',
            13 => 'مردود مشتريات',
            14 => 'أمر بيع',
            15 => 'أمر شراء',
            16 => 'عرض سعر لعميل',
            17 => 'عرض سعر من مورد',
            18 => 'فاتورة توالف',
            19 => 'أمر صرف',
            20 => 'أمر إضافة',
            21 => 'تحويل من مخزن لمخزن',
            22 => 'أمر حجز',
            24 => 'فاتورة خدمة',
            25 => 'طلب احتياج',
            26 => 'اتفاقية تسعير',
        ];

        $permissionName = 'view ' . ($titles[$type] ?? 'غير معروف');
    @endphp

    @can($permissionName)
        <livewire:invoices.view-invoice :operationId="$operationId" />
    @else
        <div class="alert alert-danger text-center">
            <i class="fas fa-lock fa-3x mb-3"></i>
            <h4>ليس لديك صلاحية لعرض {{ $titles[$type] ?? 'هذه الفاتورة' }}</h4>
        </div>
    @endcan
@endsection

@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير المخزون العام</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>الكمية</th>
                            <th>الوحدة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->aname ?? $item->name ?? '---' }}</td>
                            <td>
                                <!-- الكمية تأتي من operation_items مجموع qty_in - مجموع qty_out -->
                                {{ optional($item->operationItems)->sum(function($op) { return $op->operation_type === 'in' ? $op->qty : ($op->operation_type === 'out' ? -$op->qty : 0); }) ?? 0 }}
                            </td> <!-- ضع هنا الكمية الفعلية إذا كان هناك منطق -->
                            <td>{{ $item->main_unit->aname ?? $item->main_unit->name ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">لا توجد أصناف.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($items->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 
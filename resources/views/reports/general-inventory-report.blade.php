@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

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
                                <!-- الكمية = مجموع qtyin - مجموع qtyout من جدول operation_items حيث item_id = $item->id -->
                                {{
                                    \App\Models\OperationItems::where('item_id', $item->id)
                                        ->selectRaw('COALESCE(SUM(qty_in),0) - COALESCE(SUM(qty_out),0) as balance')
                                        ->value('balance') ?? 0
                                }}
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

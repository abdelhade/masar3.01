@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>{{ __('محلل العمل اليومي') }}</h2>
        </div>
        <div class="card-body">
            <div class="table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('التاريخ') }}</th>
                            <th>{{ __('الوقت') }}</th>
                            <th>{{ __('المستخدم') }}</th>
                            <th>{{ __('العملية') }}</th>
                            <th>{{ __('التفاصيل') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opers as $log)
                        <tr>
                            <td>{{ $log->created_at->format('Y-m-d') }}</td>
                            <td>{{ $log->created_at->format('H:i') }}</td>
                            <td>{{ $log->user->name ?? __('---') }}</td>
                            <td>{{ $log->type->ptext?? __('_____') }}</td>
                            <td>{{ $log->details }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ __('لا توجد بيانات متاحة.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

@endsection
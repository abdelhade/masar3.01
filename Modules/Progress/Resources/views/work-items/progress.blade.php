@extends('progress::layouts.app')

@section('title', __('general.progress_report'))

@section('content')
<div class="container">
    <h2>نسبة الإنجاز في البنود</h2>

    @if ($items->count())
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>اسم المشروع</th>
                    <th>اسم البند</th>
                    <th>الوحدة</th>
                    <th>الكمية المخططة</th>
                    <th>الكمية المنفذة</th>
                    <th>نسبة الإنجاز</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @foreach ($item->projects as $project)
                        <tr>
                            <td>{{ $project->name }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ $project->pivot->quantity }}</td>
                            <td>{{ $project->executed_quantity ?? 0 }}</td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $project->progress_percent ?? 0 }}%;">
                                        {{ $project->progress_percent ?? 0 }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-info text-center">لا توجد بيانات بعد.</div>
    @endif
</div>
@endsection

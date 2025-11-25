@extends('dashboard.layout')
@section('content')
    <!-- Dashboard Page Header -->
    <div class="mb-6">
        <h1 class="text-page-title mb-2">لوحة التحكم</h1>
        <p class="text-body-sm text-text-secondary">نظرة عامة على الأداء والإحصائيات</p>
    </div>
    
    @include('dashboard.charts')
@endsection

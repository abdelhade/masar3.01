@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => 'إنشاء استفسار جديد',
        'items' => [
            ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
            ['label' => 'الاستفسارات', 'url' => route('inquiries.index')],
            ['label' => 'إنشاء جديد'],
        ],
    ])
    <livewire:inquiries::create-inquiry />
@endsection

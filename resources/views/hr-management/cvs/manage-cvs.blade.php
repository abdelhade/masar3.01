@auth
    @if (auth()->user())
        @extends('admin.dashboard')
        @section('content')
            @include('components.breadcrumb', [
                'title' => __('CVs'),
                'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('CVs')]],
            ])

            <livewire:hr-management.cvs.manage-cvs />
        @endsection
    @else
        <livewire:auth.login />
    @endif


@endauth

    @extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.manufacturing')
@endsection

    @section('content')
        <livewire:edit-manufacturing-invoice :invoiceId="$id" />
    @endsection
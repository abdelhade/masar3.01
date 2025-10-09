    @extends('admin.dashboard')

    @section('content')
        <livewire:edit-manufacturing-invoice :invoiceId="$id" />
    @endsection

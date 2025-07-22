@extends('admin.dashboard')

@section('title', 'إنشاء فاتورة نقاط البيع')

@section('content')
    <div class="container-fluid">


        <div class="row">
            <div class="col-12">



            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('POS Voucher page loaded');

            // Debug Livewire events
            Livewire.on('error', (message) => {
                console.error('Livewire error:', message);
            });

            // Debug button clicks
            document.addEventListener('click', function (e) {
                if (e.target.id === 'saveButton') {
                    console.log('Save button clicked');
                }
            });
        });
    </script>
@endsection
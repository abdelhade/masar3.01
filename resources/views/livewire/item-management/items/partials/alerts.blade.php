@if (session()->has('success'))
    <div class="alert alert-success font-hold fw-bold font-12 mt-2" x-data="{ show: true }" x-show="show"
        x-init="setTimeout(() => show = false, 3000)">
        {{ session('success') }}
    </div>
@endif
@if (session()->has('error'))
    <div class="alert alert-danger font-hold fw-bold font-12 mt-2" x-data="{ show: true }" x-show="show"
        x-init="setTimeout(() => show = false, 3000)">
        {{ session('error') }}
    </div>
@endif



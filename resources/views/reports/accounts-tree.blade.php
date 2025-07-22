@extends('admin.dashboard')

@section('content')
<style>
ul {
    list-style: none;
    padding-left: 20px;
    border-left: 1px dashed #ccc;
}

.tree-item {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.toggle-icon, .no-toggle-icon {
    width: 16px;
    display: inline-block;
    text-align: center;
    font-weight: bold;
    color: #007bff;
}

.account-name {
    font-size: 14px;
}

.nested {
    margin-left: 16px;
}

.hidden {
    display: none;
}
</style>

    <div class="container">
        <div class="card">
            <div class="card-head">
                <h1>الحسابات المرتبطة</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- // accounts tree from acchead table tree by parent_id -->
                    <ul>
                        @foreach($accounts as $account)
                            @include('reports.partials.account-node', ['account' => $account])
                        @endforeach
                    </ul>

                </div>
            </div>
        </div>
    </div>

    <style>
        h2 {
            font-size: 18px;
            border: 1px solid rgb(222, 222, 222);
            padding: 5px;
        }
    </style>

    <script>
        $(document).ready(function () {
            $('#accounts-tree').DataTable();
        });
    </script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.toggle-icon');

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const parent = this.closest('li');
            const nested = parent.querySelector('.nested');

            if (nested.classList.contains('hidden')) {
                nested.classList.remove('hidden');
                this.textContent = '−';
            } else {
                nested.classList.add('hidden');
                this.textContent = '+';
            }
        });
    });
});
</script>

@endsection
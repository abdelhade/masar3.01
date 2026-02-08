@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.journals')
@endsection

@section('content')

<style>
    .form-group {
        margin-bottom: 1rem;
    }

    label {
        font-weight: 600;
        margin-bottom: 0.4rem;
        display: inline-block;
    }

    .form-control {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
        border-radius: 0.4rem;
    }

    .card-title {
        font-size: 1.3rem;
        font-weight: 700;
    }

    .card-footer {
        padding: 1.5rem 1rem;
        text-align: center;
    }

   
    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .row + .row {
        margin-top: 1rem;
    }

    .table thead th {
       
        vertical-align: middle;
        text-align: center;
    }

    .table td, .table th {
        vertical-align: middle;
    }

    .table input, .table select {
        min-width: 100px;
    }

    /* Remove overflow from table-responsive */
    .table-responsive {
        overflow: visible !important;
    }

    /* Tom Select dropdown z-index */
    .ts-dropdown,
    .tom-select-dropdown,
    .ts-dropdown-content {
        z-index: 99999 !important;
    }
</style>

<div class="">
    <div class="card mt-3">
        <div class="card-header">
            <h1 class="card-title">{{ __('Journal Entry') }}</h1>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="myForm" action="{{ route('journals.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pro_type" value="7">

                {{-- بيانات القيد --}}
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ __('Date') }}</label>
                        <input type="date" name="pro_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-3">
                        <label>{{ __('Serial Number') }}</label>
                        <input type="text" name="pro_num" class="form-control" placeholder="EX:7645">
                    </div>

                    <div class="col-md-3">
                        <label>{{ __('Employee') }}</label>
                        <select name="emp_id" class="form-control js-tom-select" required>
                            <option value="">{{ __('Select Employee') }}</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" {{ isset($default_employee_id) && $emp->id == $default_employee_id ? 'selected' : '' }}>{{ $emp->code }} - {{ $emp->aname }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>{{ __('Cost Center') }}</label>
                        <select name="cost_center" class="form-control js-tom-select" required>
                            <option value="">{{ __('Select Cost Center') }}</option>
                            @foreach ($cost_centers as $cost)
                                <option value="{{ $cost->id }}" {{ isset($default_cost_center_id) && $cost->id == $default_cost_center_id ? 'selected' : '' }}>{{ $cost->cname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <label>{{ __('Description') }}</label>
                        <input type="text" name="details" class="form-control">
                    </div>
                </div>

                {{-- الجدول --}}
                <div class="table-responsive mt-4">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th width="15%">{{ __('Debit') }}</th>
                                <th width="15%">{{ __('Credit') }}</th>
                                <th width="30%">{{ __('Account') }}</th>
                                <th width="40%">{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="number" name="debit" class="form-control debit" id="debit" value="0.00" step="0.01" required>
                                </td>
                                <td></td>
                                <td>
                                    <select name="acc1" class="form-control js-tom-select" required>
                                        <option value="">{{ __('Select Account') }}</option>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->aname }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="info2" class="form-control"></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <input type="number" name="credit" class="form-control credit" id="credit" value="0.00" step="0.01">
                                </td>
                                <td>
                                    <select name="acc2" class="form-control js-tom-select" required>
                                        <option value="">{{ __('Select Account') }}</option>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->aname }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="info3" class="form-control"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row my-4">
                    <div class="col">
                        <label>{{ __('General Notes') }}</label>
                        <input type="text" name="info" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-start">
                    <button type="submit" class="btn btn-main m-1">{{ __('Save') }}</button>
                    <button type="reset" class="btn btn-danger m-1">{{ __('Cancel') }}</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Initialize Tom Select for all searchable selects
    (function(){
        function initSelect(elem){
            if (window.TomSelect && !elem.tomselect) {
                const tomSelect = new TomSelect(elem, {
                    create: false,
                    searchField: ['text'],
                    sortField: {field: 'text', direction: 'asc'},
                    dropdownInput: true,
                    plugins: { remove_button: {title: '{{ __('Remove') }}'} },
                    placeholder: elem.getAttribute('placeholder') || '{{ __('Search...') }}'
                });
                
                // Set z-index for dropdown
                tomSelect.on('dropdown_open', function() {
                    const dropdown = elem.parentElement.querySelector('.ts-dropdown');
                    if (dropdown) {
                        dropdown.style.zIndex = '99999';
                    }
                });
            }
        }
        function initAll(){
            document.querySelectorAll('select.js-tom-select').forEach(initSelect);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    })();

    // Select all text on focus for all text inputs
    document.addEventListener('DOMContentLoaded', function() {
        const textInputs = document.querySelectorAll('input[type="text"]');
        textInputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                this.select();
            });
        });

        // Auto-sync debit to credit on keyup
        const debitInput = document.getElementById('debit');
        const creditInput = document.getElementById('credit');
        
        if (debitInput && creditInput) {
            debitInput.addEventListener('keyup', function() {
                creditInput.value = this.value;
            });
        }
    });

    // Form validation
    document.getElementById("myForm").addEventListener("submit", function(e) {
        const debit = +document.getElementById("debit").value;
        const credit = +document.getElementById("credit").value;

        if (debit !== credit) {
            e.preventDefault();
            alert("{{ __('Debit value must equal credit value.') }}");
        }
    });
</script>

@endsection

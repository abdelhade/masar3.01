@extends('admin.dash')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل عملية نقاط البيع'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('نقاط البيع'), 'url' => route('pos-vouchers.index')],
            ['label' => __('تعديل العملية')],
        ],
    ])

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="font-family-cairo fw-bold">
                            <i class="fas fa-edit me-2"></i>
                            تعديل عملية نقاط البيع
                        </h1>
                    </div>
                    <div class="col-sm-6 text-end">
                        <a href="{{ route('pos-vouchers.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                        <a href="{{ route('pos-vouchers.show', $posVoucher->id) }}" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i>
                            عرض
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> نجح!</h5>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> خطأ!</h5>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> خطأ!</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('pos-vouchers.update', $posVoucher->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Left Side - Voucher Details -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0 font-family-cairo fw-bold">
                                        <i class="fas fa-cash-register me-2"></i>
                                        تفاصيل عملية نقاط البيع
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <!-- Voucher Header -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label font-family-cairo fw-bold">رقم العملية</label>
                                            <input type="text" class="form-control font-family-cairo" value="{{ $posVoucher->pro_id }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label font-family-cairo fw-bold">التاريخ</label>
                                            <input type="date" name="pro_date" value="{{ $posVoucher->pro_date }}" class="form-control font-family-cairo" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label font-family-cairo fw-bold">الرقم الدفتري</label>
                                            <input type="text" name="pro_serial" value="{{ $posVoucher->pro_serial }}" class="form-control font-family-cairo" placeholder="الرقم الدفتري">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label font-family-cairo fw-bold">رقم الإيصال</label>
                                            <input type="text" name="pro_num" value="{{ $posVoucher->pro_num }}" class="form-control font-family-cairo" placeholder="رقم الإيصال">
                                        </div>
                                    </div>

                                    <!-- Accounts -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label font-family-cairo fw-bold">العميل</label>
                                            <select name="acc1" class="form-select font-family-cairo" required>
                                                <option value="">اختر العميل</option>
                                                @foreach($customerAccounts as $account)
                                                    <option value="{{ $account->id }}" {{ $posVoucher->acc1 == $account->id ? 'selected' : '' }}>
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label font-family-cairo fw-bold">الصندوق</label>
                                            <select name="acc2" class="form-select font-family-cairo" required>
                                                <option value="">اختر الصندوق</option>
                                                @foreach($cashAccounts as $account)
                                                    <option value="{{ $account->id }}" {{ $posVoucher->acc2 == $account->id ? 'selected' : '' }}>
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label font-family-cairo fw-bold">الموظف</label>
                                            <select name="emp_id" class="form-select font-family-cairo" required>
                                                <option value="">اختر الموظف</option>
                                                @foreach($employeeAccounts as $account)
                                                    <option value="{{ $account->id }}" {{ $posVoucher->emp_id == $account->id ? 'selected' : '' }}>
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Items Table -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="itemsTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th class="font-family-cairo fw-bold">المنتج</th>
                                                    <th class="font-family-cairo fw-bold">الوحدة</th>
                                                    <th class="font-family-cairo fw-bold">الكمية</th>
                                                    <th class="font-family-cairo fw-bold">السعر</th>
                                                    <th class="font-family-cairo fw-bold">الإجمالي</th>
                                                    <th class="font-family-cairo fw-bold">إجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($posVoucher->operationItems as $index => $item)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $item->item->name ?? '-' }}</strong>
                                                            <br>
                                                            <small class="text-muted">كود: {{ $item->item->code ?? '-' }}</small>
                                                            <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->item_id }}">
                                                        </td>
                                                        <td>
                                                            <select name="items[{{ $index }}][unit_id]" class="form-select form-select-sm" required>
                                                                @if($item->item)
                                                                    @foreach($item->item->units as $unit)
                                                                        <option value="{{ $unit->id }}" {{ $item->unit_id == $unit->id ? 'selected' : '' }}>
                                                                            {{ $unit->name }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[{{ $index }}][quantity]" 
                                                                   value="{{ $item->qty_in }}" 
                                                                   class="form-control form-control-sm item-quantity" 
                                                                   min="0.01" 
                                                                   step="0.01" 
                                                                   required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[{{ $index }}][price]" 
                                                                   value="{{ $item->item_price }}" 
                                                                   class="form-control form-control-sm item-price" 
                                                                   min="0" 
                                                                   step="0.01" 
                                                                   required>
                                                        </td>
                                                        <td class="text-end font-weight-bold item-total">
                                                            {{ number_format($item->detail_value, 2) }}
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Totals -->
                                    <div class="row">
                                        <div class="col-md-6 offset-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="font-family-cairo fw-bold">المجموع الفرعي:</td>
                                                    <td class="text-end" id="subtotal">{{ number_format($posVoucher->pro_value, 2) }}</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td class="font-family-cairo fw-bold h5">الإجمالي:</td>
                                                    <td class="text-end h5 font-weight-bold" id="total">{{ number_format($posVoucher->pro_value, 2) }}</td>
                                                </tr>
                                            </table>
                                            <input type="hidden" name="pro_value" id="pro_value" value="{{ $posVoucher->pro_value }}">
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="row mt-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-save me-1"></i>
                                                حفظ التعديلات
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Categories and Items -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0 font-family-cairo fw-bold">
                                        <i class="fas fa-tags me-2"></i>
                                        التصنيفات والمنتجات
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Categories -->
                                    <div class="mb-4">
                                        <h6 class="font-family-cairo fw-bold mb-3">التصنيفات</h6>
                                        <div class="list-group" id="categoriesList">
                                            @foreach($notes as $note)
                                                <div class="list-group-item list-group-item-action cursor-pointer" 
                                                     data-note-id="{{ $note->id }}"
                                                     style="cursor: pointer;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="font-family-cairo fw-bold">{{ $note->name }}</span>
                                                        <i class="fas fa-chevron-right"></i>
                                                    </div>
                                                </div>
                                                
                                                <div class="ms-3 mt-2 note-details" id="note-details-{{ $note->id }}" style="display: none;">
                                                    @foreach($note->noteDetails as $noteDetail)
                                                        <div class="list-group-item list-group-item-action cursor-pointer note-detail-item" 
                                                             data-note-detail-id="{{ $noteDetail->id }}"
                                                             style="cursor: pointer;">
                                                            <span class="font-family-cairo">{{ $noteDetail->name }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Items Grid -->
                                    <div id="itemsGrid" style="display: none;">
                                        <h6 class="font-family-cairo fw-bold mb-3">المنتجات</h6>
                                        <div class="row" id="itemsContainer">
                                            <!-- Items will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .font-family-cairo {
        font-family: 'Cairo', sans-serif;
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .cursor-pointer:hover {
        background-color: #f8f9fa;
    }
    
    .list-group-item.active {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .list-group-item.active:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .btn {
        font-family: 'Cairo', sans-serif;
    }
    
    .form-control, .form-select {
        font-family: 'Cairo', sans-serif;
    }
    
    .table th, .table td {
        font-family: 'Cairo', sans-serif;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let itemIndex = {{ count($posVoucher->operationItems) }};
        
        // Category selection
        $('.list-group-item[data-note-id]').click(function() {
            const noteId = $(this).data('note-id');
            
            // Hide all note details
            $('.note-details').hide();
            $('.list-group-item[data-note-id]').removeClass('active');
            
            // Show selected note details
            $(this).addClass('active');
            $(`#note-details-${noteId}`).show();
        });
        
        // Note detail selection
        $('.note-detail-item').click(function() {
            const noteDetailId = $(this).data('note-detail-id');
            
            // Load items for this note detail
            $.get(`/pos-vouchers/get-items-by-note-detail`, {
                note_detail_id: noteDetailId
            })
            .done(function(items) {
                displayItems(items);
            })
            .fail(function() {
                alert('حدث خطأ أثناء تحميل المنتجات');
            });
        });
        
        function displayItems(items) {
            const container = $('#itemsContainer');
            container.empty();
            
            items.forEach(function(item) {
                const itemCard = `
                    <div class="col-6 mb-2">
                        <div class="card border cursor-pointer add-item-card" 
                             data-item-id="${item.id}"
                             data-item-name="${item.name}"
                             data-item-code="${item.code}"
                             style="cursor: pointer;">
                            <div class="card-body p-2 text-center">
                                <h6 class="card-title font-family-cairo fw-bold mb-1" style="font-size: 0.9rem;">
                                    ${item.name}
                                </h6>
                                <small class="text-muted d-block mb-1">كود: ${item.code}</small>
                                ${item.prices && item.prices.length > 0 ? 
                                    `<span class="badge bg-primary">${parseFloat(item.prices[0].pivot.price).toFixed(2)}</span>` : 
                                    '<span class="badge bg-secondary">لا يوجد سعر</span>'
                                }
                            </div>
                        </div>
                    </div>
                `;
                container.append(itemCard);
            });
            
            $('#itemsGrid').show();
        }
        
        // Add item to table
        $(document).on('click', '.add-item-card', function() {
            const itemId = $(this).data('item-id');
            const itemName = $(this).data('item-name');
            const itemCode = $(this).data('item-code');
            
            // Get item details via AJAX
            $.get(`/items/${itemId}/json`)
            .done(function(item) {
                addItemToTable(item, itemIndex++);
            })
            .fail(function() {
                alert('حدث خطأ أثناء إضافة المنتج');
            });
        });
        
        function addItemToTable(item, index) {
            const units = item.units || [];
            const prices = item.prices || [];
            
            const unitOptions = units.map(unit => 
                `<option value="${unit.id}">${unit.name}</option>`
            ).join('');
            
            const defaultPrice = prices.length > 0 ? prices[0].pivot.price : 0;
            
            const newRow = `
                <tr>
                    <td>
                        <strong>${item.name}</strong>
                        <br>
                        <small class="text-muted">كود: ${item.code}</small>
                        <input type="hidden" name="items[${index}][item_id]" value="${item.id}">
                    </td>
                    <td>
                        <select name="items[${index}][unit_id]" class="form-select form-select-sm" required>
                            ${unitOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" 
                               value="1" 
                               class="form-control form-control-sm item-quantity" 
                               min="0.01" 
                               step="0.01" 
                               required>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][price]" 
                               value="${defaultPrice}" 
                               class="form-control form-control-sm item-price" 
                               min="0" 
                               step="0.01" 
                               required>
                    </td>
                    <td class="text-end font-weight-bold item-total">
                        ${defaultPrice.toFixed(2)}
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#itemsTable tbody').append(newRow);
            calculateTotals();
        }
        
        // Remove item from table
        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
        });
        
        // Calculate totals when quantity or price changes
        $(document).on('input', '.item-quantity, .item-price', function() {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            const price = parseFloat(row.find('.item-price').val()) || 0;
            const total = quantity * price;
            
            row.find('.item-total').text(total.toFixed(2));
            calculateTotals();
        });
        
        function calculateTotals() {
            let subtotal = 0;
            $('.item-total').each(function() {
                subtotal += parseFloat($(this).text()) || 0;
            });
            
            $('#subtotal').text(subtotal.toFixed(2));
            $('#total').text(subtotal.toFixed(2));
            $('#pro_value').val(subtotal);
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@endpush

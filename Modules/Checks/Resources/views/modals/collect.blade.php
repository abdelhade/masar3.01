<!-- Modal تحصيل الشيكات -->
<div class="modal fade" id="collectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-university"></i> تحصيل الشيكات</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="collectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر البنك <span class="text-danger">*</span></label>
                        <select name="bank_account_id" id="bank_account_id" class="form-select" required>
                            <option value="">اختر البنك</option>
                            @php
                                $banks = \Modules\Accounts\Models\AccHead::where('is_basic', 0)
                                    ->where('isdeleted', 0)
                                    ->where('code', 'like', '1102%')
                                    ->select('id', 'aname', 'code', 'balance')
                                    ->get();
                            @endphp
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">
                                    {{ $bank->aname }} - {{ $bank->code }} 
                                    (رصيد: {{ number_format($bank->balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ التحصيل <span class="text-danger">*</span></label>
                        <input type="date" name="collection_date" id="collection_date" 
                               class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        سيتم تحويل الشيكات المحددة إلى البنك المختار وإنشاء القيد المحاسبي
                    </div>

                    <div id="selectedChecksInfo"></div>
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? 1 }}">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> تحصيل الآن
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function collectSelected() {
    const selected = $('.check-item:checked').map(function() {
        return this.value;
    }).get();
    
    if(selected.length === 0) {
        alert('يرجى اختيار شيكات أولاً');
        return;
    }
    
    $('#selectedChecksInfo').html(`
        <div class="alert alert-success">
            <strong>عدد الشيكات المحددة:</strong> ${selected.length}
        </div>
    `);
    
    $('#collectModal').modal('show');
    
    $('#collectForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            _token: '{{ csrf_token() }}',
            ids: selected,
            bank_account_id: $('#bank_account_id').val(),
            collection_date: $('#collection_date').val(),
            branch_id: $('input[name="branch_id"]').val()
        };
        
        $.post('/checks/batch-collect', formData, function(response) {
            $('#collectModal').modal('hide');
            if(response.success) {
                location.reload();
            } else {
                alert(response.message || 'حدث خطأ');
            }
        }).fail(function(xhr) {
            alert('حدث خطأ: ' + (xhr.responseJSON?.message || 'خطأ في الاتصال'));
        });
    });
}
</script>

<!-- Modal ØªØ­ØµÙŠÙ„ Ø§Ù„Ø´ÙŠÙƒØ§Øª -->
<div class="modal fade" id="collectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-university"></i> ØªØ­ØµÙŠÙ„ Ø§Ù„Ø´ÙŠÙƒØ§Øª</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="collectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ø§Ø®ØªØ± Ø§Ù„Ø¨Ù†Ùƒ <span class="text-danger">*</span></label>
                        <select name="bank_account_id" id="bank_account_id" class="form-select" required>
                            <option value="">Ø§Ø®ØªØ± Ø§Ù„Ø¨Ù†Ùƒ</option>
                            @php
                                $banks = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
                                    ->where('is_basic', 0)
                                    ->where('code', 'like', '1102%')
                                    ->select('id', 'aname', 'code', 'balance')
                                    ->get();
                            @endphp
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">
                                    {{ $bank->aname }} - {{ $bank->code }} 
                                    (Ø±ØµÙŠØ¯: {{ number_format($bank->balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ØªØ§Ø±ÙŠØ® Ø§Ù„ØªØ­ØµÙŠÙ„ <span class="text-danger">*</span></label>
                        <input type="date" name="collection_date" id="collection_date" 
                               class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Ø³ÙŠØªÙ… ØªØ­ÙˆÙŠÙ„ Ø§Ù„Ø´ÙŠÙƒØ§Øª Ø§Ù„Ù…Ø­Ø¯Ø¯Ø© Ø¥Ù„Ù‰ Ø§Ù„Ø¨Ù†Ùƒ Ø§Ù„Ù…Ø®ØªØ§Ø± ÙˆØ¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ù‚ÙŠØ¯ Ø§Ù„Ù…Ø­Ø§Ø³Ø¨ÙŠ
                    </div>

                    <div id="selectedChecksInfo"></div>
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? 1 }}">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Ø¥Ù„ØºØ§Ø¡
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> ØªØ­ØµÙŠÙ„ Ø§Ù„Ø¢Ù†
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
        alert('ÙŠØ±Ø¬Ù‰ Ø§Ø®ØªÙŠØ§Ø± Ø´ÙŠÙƒØ§Øª Ø£ÙˆÙ„Ø§Ù‹');
        return;
    }
    
    $('#selectedChecksInfo').html(`
        <div class="alert alert-success">
            <strong>Ø¹Ø¯Ø¯ Ø§Ù„Ø´ÙŠÙƒØ§Øª Ø§Ù„Ù…Ø­Ø¯Ø¯Ø©:</strong> ${selected.length}
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
                alert(response.message || 'Ø­Ø¯Ø« Ø®Ø·Ø£');
            }
        }).fail(function(xhr) {
            alert('Ø­Ø¯Ø« Ø®Ø·Ø£: ' + (xhr.responseJSON?.message || 'Ø®Ø·Ø£ ÙÙŠ Ø§Ù„Ø§ØªØµØ§Ù„'));
        });
    });
}
</script>


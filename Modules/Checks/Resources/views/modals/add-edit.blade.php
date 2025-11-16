<!-- Modal Ø¥Ø¶Ø§ÙØ©/ØªØ¹Ø¯ÙŠÙ„ Ø´ÙŠÙƒ -->
<div class="modal fade" id="addEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Ø¥Ø¶Ø§ÙØ© ÙˆØ±Ù‚Ø© Ø¬Ø¯ÙŠØ¯Ø©</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="checkForm" method="POST" action="{{ route('checks.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="checkId">
                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? 1 }}">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ø±Ù‚Ù… Ø§Ù„Ø´ÙŠÙƒ <span class="text-danger">*</span></label>
                            <input type="text" name="check_number" id="check_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ø§Ø³Ù… Ø§Ù„Ø¨Ù†Ùƒ <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" id="bank_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ø±Ù‚Ù… Ø§Ù„Ø­Ø³Ø§Ø¨ <span class="text-danger">*</span></label>
                            <input type="text" name="account_number" id="account_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ØµØ§Ø­Ø¨ Ø§Ù„Ø­Ø³Ø§Ø¨ <span class="text-danger">*</span></label>
                            <input type="text" name="account_holder_name" id="account_holder_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ø§Ù„Ù…Ø¨Ù„Øº <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¥ØµØ¯Ø§Ø± <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚ <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="due_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ø§Ù„Ø­Ø§Ù„Ø©</label>
                            <select name="status" id="status" class="form-select">
                                <option value="pending">Ù…Ø¹Ù„Ù‚</option>
                                <option value="cleared">Ù…ØµÙÙ‰</option>
                                <option value="bounced">Ù…Ø±ØªØ¯</option>
                                <option value="cancelled">Ù…Ù„ØºÙ‰</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ø§Ù„Ù†ÙˆØ¹</label>
                            <select name="type" id="type" class="form-select" onchange="updateAccountLabel()">
                                <option value="incoming" {{ isset($pageType) && $pageType === 'incoming' ? 'selected' : '' }}>ÙˆØ±Ù‚Ø© Ù‚Ø¨Ø¶</option>
                                <option value="outgoing" {{ isset($pageType) && $pageType === 'outgoing' ? 'selected' : '' }}>ÙˆØ±Ù‚Ø© Ø¯ÙØ¹</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ø±Ù‚Ù… Ø§Ù„Ù…Ø±Ø¬Ø¹</label>
                            <input type="text" name="reference_number" id="reference_number" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" id="acc_label">Ø§Ù„Ø­Ø³Ø§Ø¨ Ø§Ù„Ù…Ø±ØªØ¨Ø· (Ø¹Ù…ÙŠÙ„/Ù…ÙˆØ±Ø¯)</label>
                            <select name="acc_id" id="acc_id" class="form-select">
                                <option value="">Ø§Ø®ØªØ± Ø§Ù„Ø­Ø³Ø§Ø¨</option>
                                @php
                                    $clients = \Modules\Accounts\Models\AccHead::where('code', 'like', '1103%')->get();
                                    $suppliers = \Modules\Accounts\Models\AccHead::where('code', 'like', '2101%')->get();
                                @endphp
                                <optgroup label="Ø§Ù„Ø¹Ù…Ù„Ø§Ø¡" id="clients_group">
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->aname }} - {{ $client->code }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Ø§Ù„Ù…ÙˆØ±Ø¯ÙŠÙ†" id="suppliers_group" style="display:none;">
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->aname }} - {{ $supplier->code }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <small class="text-muted">Ø§Ø®ØªÙŠØ§Ø±ÙŠ - ÙŠÙ…ÙƒÙ† ØªØ±ÙƒÙ‡ ÙØ§Ø±ØºØ§Ù‹</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ø§Ø³Ù… Ø§Ù„Ù…Ø³ØªÙÙŠØ¯</label>
                            <input type="text" name="payee_name" id="payee_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ø§Ø³Ù… Ø§Ù„Ø¯Ø§ÙØ¹</label>
                            <input type="text" name="payer_name" id="payer_name" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ù…Ù„Ø§Ø­Ø¸Ø§Øª</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Ø¥Ù„ØºØ§Ø¡
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ø­ÙØ¸
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateAccountLabel() {
    const type = $('#type').val();
    const clientsGroup = $('#clients_group');
    const suppliersGroup = $('#suppliers_group');
    const accLabel = $('#acc_label');
    
    if (type === 'incoming') {
        accLabel.text('Ø§Ù„Ø¹Ù…ÙŠÙ„');
        clientsGroup.show();
        suppliersGroup.hide();
        // Reset to first client option
        $('#acc_id option').prop('selected', false);
        $('#clients_group option:first').prop('selected', true);
    } else {
        accLabel.text('Ø§Ù„Ù…ÙˆØ±Ø¯');
        clientsGroup.hide();
        suppliersGroup.show();
        // Reset to first supplier option
        $('#acc_id option').prop('selected', false);
        $('#suppliers_group option:first').prop('selected', true);
    }
}

// Initialize on page load
$(document).ready(function() {
    updateAccountLabel();
});
</script>


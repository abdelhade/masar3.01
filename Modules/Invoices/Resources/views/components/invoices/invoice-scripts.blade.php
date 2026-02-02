{{--
    Invoice Scripts – Vanilla JS only (no Alpine.js)
    إضافة صنف، حذف صف، التحقق من النموذج، تحديث الرصيد، البحث عن الأصناف (صفحة الإنشاء).
--}}

<style>
    .invoice-search-results { display: none; }
    .invoice-search-results.is-visible { display: block; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function() {
    'use strict';

    if (typeof window.formatNumber !== 'function') {
        window.formatNumber = function(num) {
            if (num === null || num === undefined || isNaN(num)) return '0';
            var numStr = parseFloat(num).toString();
            if (numStr.indexOf('.') === -1)
                return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            var parts = numStr.split('.');
            parts[1] = parts[1].replace(/0+$/, '');
            if (parts[1] === '') return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '.' + parts[1];
        };
    }
    if (typeof window.formatNumberFixed !== 'function') {
        window.formatNumberFixed = function(num, decimals) {
            decimals = decimals === undefined ? 2 : decimals;
            if (num === null || num === undefined || isNaN(num)) return '0';
            var formatted = parseFloat(num).toFixed(decimals);
            return formatted.replace(/\.?0+$/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        };
    }

    /** التنقل بالـ Enter بين حقول الفاتورة (vanilla) */
    window.handleEnterNavigation = function(event) {
        var form = event.target.closest('form');
        if (!form) return;
        var fields = form.querySelectorAll('.invoice-field[data-field]:not([readonly])');
        if (!fields.length) return;
        var current = event.target;
        var idx = -1;
        for (var i = 0; i < fields.length; i++) {
            if (fields[i] === current) { idx = i; break; }
        }
        if (idx < 0) return;
        event.preventDefault();
        var nextIdx = idx + 1;
        if (nextIdx < fields.length) {
            fields[nextIdx].focus();
            if (fields[nextIdx].select) fields[nextIdx].select();
        }
    };

    /** إضافة صنف بالمعرف (getItemRowData + applyItemRowData) */
    window.invoiceAddItemById = function(itemId) {
        var form = document.querySelector('.invoice-scroll-container');
        if (!form) form = document.querySelector('form[data-invoice-submit]');
        if (!form) return;
        form = form.closest ? form.closest('form') : form;
        var root = form.closest('[wire\\:id]');
        var wireId = root ? root.getAttribute('wire:id') : (form.getAttribute('wire:id') || null);
        if (!wireId || typeof Livewire === 'undefined') return;
        var wire = Livewire.find(wireId);
        if (!wire) return;
        wire.call('getItemRowData', itemId).then(function(result) {
            if (!result || !result.success) return;
            wire.call('applyItemRowData', result).then(function(applyResult) {
                if (applyResult && applyResult.success && applyResult.index !== undefined) {
                    setTimeout(function() {
                        var qtyEl = document.getElementById('quantity-' + applyResult.index);
                        if (qtyEl) { qtyEl.focus(); if (qtyEl.select) qtyEl.select(); }
                    }, 100);
                }
            });
        }).catch(function(err) { console.error('invoiceAddItemById:', err); });
    };

    function invoiceGetFormWire() {
        var form = document.querySelector('.invoice-scroll-container');
        if (!form) form = document.querySelector('form[data-invoice-submit]');
        if (form && form.closest) form = form.closest('form');
        if (!form) return null;
        var root = form.closest('[wire\\:id]');
        var wireId = root ? root.getAttribute('wire:id') : form.getAttribute('wire:id');
        return wireId && typeof Livewire !== 'undefined' ? Livewire.find(wireId) : null;
    }

    function setupInvoiceVanillaJS() {
        var form = document.querySelector('.invoice-scroll-container');
        if (!form) form = document.querySelector('form[data-invoice-submit]');
        if (form && form.closest) form = form.closest('form');
        if (!form) return;
        var root = form.closest('[wire\\:id]');
        var wireId = root ? root.getAttribute('wire:id') : form.getAttribute('wire:id');
        var wire = wireId && typeof Livewire !== 'undefined' ? Livewire.find(wireId) : null;
        if (!wire) return;

        var container = document.querySelector('.invoice-scroll-container');
        if (container && !container._invoiceRemoveBound) {
            container._invoiceRemoveBound = true;
            container.addEventListener('click', function(e) {
                var btn = e.target.closest('button[wire\\:click*="removeRow"]');
                if (!btn) return;
                var tr = btn.closest('tr[data-row-index]');
                if (!tr) return;
                e.preventDefault();
                e.stopPropagation();
                var index = parseInt(tr.getAttribute('data-row-index'), 10);
                if (!isNaN(index)) wire.call('removeRow', index);
            });
        }

        var submitMethod = form.getAttribute('data-invoice-submit') || 'saveForm';
        if (!form._invoiceEnterBound) {
            form._invoiceEnterBound = true;
            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName === 'BUTTON' && e.target.type === 'submit') {
                    e.preventDefault();
                    e.target.click();
                }
            });
        }
        if (!form._invoiceValidateBound) {
            form._invoiceValidateBound = true;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                wire.get('acc1_id').then(function(acc1) {
                    wire.get('acc2_id').then(function(acc2) {
                        wire.get('invoiceItems').then(function(items) {
                            var hasItems = Array.isArray(items) && items.length > 0;
                            var hasAcc1 = acc1 != null && acc1 !== '' && String(acc1) !== 'null';
                            var hasAcc2 = acc2 != null && acc2 !== '' && String(acc2) !== 'null';
                            if (!hasAcc1) {
                                if (window.Swal) window.Swal.fire({ icon: 'warning', title: '{{ is_string($t = __("Validation")) ? e($t) : "Validation" }}', text: '{{ is_string($t = __("Please select account (client/supplier).")) ? e($t) : "Please select account (client/supplier)." }}' });
                                return;
                            }
                            if (!hasAcc2) {
                                if (window.Swal) window.Swal.fire({ icon: 'warning', title: '{{ is_string($t = __("Validation")) ? e($t) : "Validation" }}', text: '{{ is_string($t = __("Please select store.")) ? e($t) : "Please select store." }}' });
                                return;
                            }
                            if (!hasItems) {
                                if (window.Swal) window.Swal.fire({ icon: 'warning', title: '{{ is_string($t = __("Validation")) ? e($t) : "Validation" }}', text: '{{ is_string($t = __("Please add at least one item.")) ? e($t) : "Please add at least one item." }}' });
                                return;
                            }
                            wire.call(submitMethod);
                        });
                    });
                });
            }, true);
        }

        var balanceEl = document.getElementById('invoice-balance-after');
        if (balanceEl && !form._invoiceBalanceBound) {
            form._invoiceBalanceBound = true;
            var balanceDebounce;
            function updateBalanceDisplay() {
                balanceDebounce = null;
                wire.call('getBalanceAfter').then(function(r) {
                    if (r && balanceEl) {
                        var val = parseFloat(r.balance_after);
                        balanceEl.textContent = window.formatNumberFixed ? window.formatNumberFixed(val) : (isNaN(val) ? '0' : val.toFixed(2));
                        balanceEl.className = (balanceEl.className || '').replace(/\btext-(danger|success)\b/g, '') + (val < 0 ? ' text-danger' : ' text-success');
                    }
                });
            }
            form.addEventListener('input', function() {
                if (balanceDebounce) clearTimeout(balanceDebounce);
                balanceDebounce = setTimeout(updateBalanceDisplay, 500);
            });
            form.addEventListener('change', function() {
                if (balanceDebounce) clearTimeout(balanceDebounce);
                balanceDebounce = setTimeout(updateBalanceDisplay, 500);
            });
        }
    }

    /** البحث عن الأصناف (صفحة الإنشاء فقط) – Fuse.js + /api/items/lite */
    function setupInvoiceSearch() {
        var wrap = document.getElementById('invoice-search-container');
        var searchInput = document.getElementById('search-input');
        var resultsEl = document.getElementById('invoice-search-results');
        if (!wrap || !searchInput || !resultsEl) return;

        var branchId = wrap.getAttribute('data-branch-id') || '';
        var invoiceType = parseInt(wrap.getAttribute('data-invoice-type') || '10', 10);
        var allItems = [];
        var fuse = null;
        var searchDebounce = null;

        function loadItems() {
            var url = '/api/items/lite?branch_id=' + encodeURIComponent(branchId) + '&type=' + invoiceType;
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.ok ? r.json() : []; })
                .then(function(data) {
                    if (Array.isArray(data)) {
                        allItems = data;
                        if (window.Fuse) {
                            fuse = new window.Fuse(allItems, { keys: ['name', 'code', 'barcode'], threshold: 0.3, ignoreLocation: true });
                        }
                    }
                })
                .catch(function() {});
        }

        function renderResults(term) {
            term = (term || '').trim();
            if (term.length < 1) {
                resultsEl.classList.remove('is-visible');
                resultsEl.innerHTML = '';
                return;
            }
            var list = [];
            if (fuse) {
                var res = fuse.search(term);
                list = (res || []).slice(0, 50).map(function(r) { return r.item; });
            }
            var html = '';
            if (list.length) {
                list.forEach(function(item, i) {
                    var name = (item.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    var code = (item.code || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    html += '<li class="list-group-item list-group-item-action js-invoice-search-item" data-item-id="' + (item.id || '') + '" style="cursor:pointer;"><strong>' + name + '</strong>';
                    if (code) html += ' <small class="text-muted">- ' + code + '</small>';
                    html += '</li>';
                });
            } else {
                html = '<li class="list-group-item list-group-item-action list-group-item-success js-invoice-search-item-create" data-search-term="' + (term || '').replace(/"/g, '&quot;') + '" style="cursor:pointer;"><i class="fas fa-plus"></i> <strong>{{ is_string($t = __("Create new item")) ? e($t) : "Create new item" }}</strong>: ' + (term || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</li>';
            }
            resultsEl.innerHTML = html;
            resultsEl.classList.add('is-visible');

            resultsEl.querySelectorAll('.js-invoice-search-item').forEach(function(li) {
                li.addEventListener('click', function() {
                    var id = li.getAttribute('data-item-id');
                    if (id && window.invoiceAddItemById) {
                        window.invoiceAddItemById(parseInt(id, 10));
                        resultsEl.classList.remove('is-visible');
                        searchInput.value = '';
                    }
                });
            });
            resultsEl.querySelectorAll('.js-invoice-search-item-create').forEach(function(li) {
                li.addEventListener('click', function() {
                    var term = li.getAttribute('data-search-term') || '';
                    resultsEl.classList.remove('is-visible');
                    searchInput.value = '';
                    if (!term || typeof Livewire === 'undefined') return;
                    var wire = invoiceGetFormWire();
                    if (wire) wire.call('createNewItem', term);
                });
            });
        }

        searchInput.addEventListener('input', function() {
            var term = searchInput.value;
            if (searchDebounce) clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function() { renderResults(term); }, 80);
        });
        searchInput.addEventListener('focus', function() {
            if ((searchInput.value || '').trim().length > 0 || resultsEl.innerHTML) resultsEl.classList.add('is-visible');
        });
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                resultsEl.classList.remove('is-visible');
                searchInput.blur();
            }
        });
        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target)) resultsEl.classList.remove('is-visible');
        });

        loadItems();
    }

    function setupInstallmentButton() {
        document.getElementById('btn-installment-invoice') && document.getElementById('btn-installment-invoice').addEventListener('click', function() {
            var btn = this;
            var clientId = btn.getAttribute('data-client');
            var finalTotal = parseFloat(btn.getAttribute('data-total')) || 0;
            if (!clientId || clientId === 'null' || clientId === '') {
                if (window.Swal) window.Swal.fire({ icon: 'warning', title: 'تحذير', text: 'يرجى اختيار العميل في الفاتورة أولاً', confirmButtonText: 'حسناً' });
                return;
            }
            var modalEl = document.getElementById('installmentModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
                setTimeout(function() {
                    var totalInput = document.getElementById('totalAmount');
                    if (totalInput) { totalInput.value = finalTotal; totalInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    var clientSelect = document.getElementById('accHeadId');
                    if (clientSelect) { clientSelect.value = clientId; clientSelect.dispatchEvent(new Event('change', { bubbles: true })); }
                }, 300);
            }
        });
    }

    document.addEventListener('livewire:load', function() {
        setTimeout(setupInvoiceVanillaJS, 100);
        setTimeout(setupInvoiceSearch, 150);
        setTimeout(setupInstallmentButton, 200);
    });
    document.addEventListener('livewire:navigated', function() {
        setTimeout(setupInvoiceVanillaJS, 100);
        setTimeout(setupInvoiceSearch, 150);
        setTimeout(setupInstallmentButton, 200);
    });
})();
</script>

{{-- Livewire Events --}}
<script>
{{-- لا نضيف enlarge-menu في صفحات الفواتير: في app.css يخفي السايد بار (display:none). صفحة الفاتورة تستخدم body.invoice-page.sidebar-collapsed لتصغير السايد بار إلى 80px فقط. --}}

document.addEventListener('livewire:init', function() {
    if (typeof Livewire === 'undefined') return;
    Livewire.on('swal', function(data) {
        Swal.fire({ title: data.title, text: data.text, icon: data.icon }).then(function() { location.reload(); });
    });
    Livewire.on('error', function(data) {
        Swal.fire({ title: data.title, text: data.text, icon: data.icon });
    });
    Livewire.on('success', function(data) {
        Swal.fire({ title: data.title, text: data.text, icon: data.icon });
    });
    Livewire.on('open-print-window', function(event) {
        var w = window.open(event.url, '_blank');
        if (w) w.onload = function() { w.print(); };
        else alert("{{ is_string($t = __('Please allow pop-ups in your browser for printing.')) ? e($t) : 'Please allow pop-ups in your browser for printing.' }}");
    });
    Livewire.on('focus-quantity', function(event) {
        var index = event.index;
        if (index == null) return;
        setTimeout(function() {
            var el = document.getElementById('quantity-' + index);
            if (el) { el.focus(); if (el.select) el.select(); }
        }, 300);
    });
    Livewire.on('focus-field', function(event) {
        setTimeout(function() {
            var el = document.getElementById((event.field || 'quantity') + '-' + (event.rowIndex || 0));
            if (el) { el.focus(); if (el.select) el.select(); }
        }, 100);
    });
    Livewire.on('focus-search-field', function() {
        setTimeout(function() {
            var el = document.getElementById('search-input');
            if (el) { el.focus(); if (el.select) el.select(); }
        }, 100);
    });
    Livewire.on('alert', function(data) {
        if (data && data.type && data.message) {
            var icon = data.type === 'error' ? 'error' : data.type === 'success' ? 'success' : data.type === 'info' ? 'info' : 'warning';
            Swal.fire({ icon: icon, title: data.message, showConfirmButton: true, timer: 3000, timerProgressBar: true });
        }
    });
    Livewire.on('item-not-found', function(event) {
        window.dispatchEvent(new CustomEvent('item-not-found', { detail: event }));
    });
});

document.addEventListener('livewire:initialized', function() {
    if (typeof Livewire === 'undefined') return;
    Livewire.on('prompt-create-item-from-barcode', function(event) {
        Swal.fire({
            title: "{{ is_string($t = __('Item not found!')) ? e($t) : 'Item not found!' }}",
            text: "{{ is_string($t = __('Barcode ')) ? e($t) : 'Barcode ' }}\"" + (event.barcode || '') + "\"{{ is_string($t = __(' is not registered. Do you want to create a new item?')) ? e($t) : ' is not registered. Do you want to create a new item?' }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: "{{ is_string($t = __('Yes, create it')) ? e($t) : 'Yes, create it' }}",
            cancelButtonText: "{{ is_string($t = __('Cancel')) ? e($t) : 'Cancel' }}",
            input: 'text',
            inputLabel: "{{ is_string($t = __('Please enter the new item name')) ? e($t) : 'Please enter the new item name' }}",
            inputPlaceholder: "{{ is_string($t = __('Type the item name here...')) ? e($t) : 'Type the item name here...' }}",
            inputValidator: function(value) { return !value && "{{ is_string($t = __('Item name is required!')) ? e($t) : 'Item name is required!' }}"; }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                var form = document.querySelector('form[data-invoice-submit]') || document.querySelector('.invoice-scroll-container');
                if (form && form.closest) form = form.closest('form');
                var root = form ? form.closest('[wire\\:id]') : null;
                var wireId = root ? root.getAttribute('wire:id') : null;
                var wire = wireId ? Livewire.find(wireId) : null;
                if (!wire && typeof Livewire !== 'undefined' && Livewire.all().length) wire = Livewire.all()[0];
                if (wire) {
                    wire.call('createItemFromPrompt', result.value, event.barcode).then(function(response) {
                        if (response && (response.success || response.index !== undefined)) {
                            setTimeout(function() {
                                var q = document.getElementById('quantity-' + (response.index || 0));
                                if (q) { q.focus(); if (q.select) q.select(); }
                            }, 200);
                        }
                    }).catch(function(err) {
                        console.error(err);
                        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل في إنشاء الصنف' });
                    });
                }
            }
        });
    });
});

document.addEventListener('item-not-found', function(event) {
    var detail = event.detail || {};
    var term = detail.term || '';
    var type = detail.type || 'barcode';
    var title = "{{ is_string($t = __('Item not found')) ? e($t) : 'Item not found' }}";
    var text = type === 'barcode'
        ? "{{ is_string($t = __('The item with the entered barcode was not found. Do you want to add a new item?')) ? e($t) : 'The item with the entered barcode was not found. Do you want to add a new item?' }}"
        : "{{ is_string($t = __('Item ')) ? e($t) : 'Item ' }}\"" + (term || '') + "\"{{ is_string($t = __(' not found. Do you want to add a new item?')) ? e($t) : ' not found. Do you want to add a new item?' }}";
    Swal.fire({ title: title, text: text, icon: 'warning', showCancelButton: true, confirmButtonText: "{{ is_string($t = __('Yes, add item')) ? e($t) : 'Yes, add item' }}", cancelButtonText: "{{ is_string($t = __('No')) ? e($t) : 'No' }}", allowEscapeKey: true })
        .then(function(result) {
            if (result.isConfirmed) {
                var url = type === 'barcode' ? "{{ route('items.create') }}?barcode=" + encodeURIComponent(term) : "{{ route('items.create') }}?name=" + encodeURIComponent(term);
                window.open(url, '_blank');
            }
        });
});
</script>

{{--
    Invoice Scripts – Pure AJAX / Client Side Logic
    No Livewire Events Overhead
--}}

<style>
    .invoice-search-results {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        max-height: 350px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ced4da;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .invoice-search-results.is-visible { display: block; }
    .search-item-row { padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #eee; }
    .search-item-row:hover, .search-item-row.active { background-color: #f8f9fa; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function() {
    'use strict';

    // إعدادات CSRF Token لطلبات الـ POST
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // -------------------------------------------------------------------------
    // 1. Core Functions (AJAX)
    // -------------------------------------------------------------------------
    
    async function postData(url, data = {}) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    // -------------------------------------------------------------------------
    // 2. Search Logic
    // -------------------------------------------------------------------------
    function initSearch() {
        const searchInput = document.getElementById('search-input');
        const resultsContainer = document.getElementById('invoice-search-results');
        
        if (!searchInput || !resultsContainer) return;

        let debounceTimer;
        let abortCtrl;

        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.trim();
            if (abortCtrl) abortCtrl.abort();

            if (term.length < 2) {
                resultsContainer.classList.remove('is-visible');
                return;
            }

            abortCtrl = new AbortController();
            fetch(`/api/search?q=${encodeURIComponent(term)}`, { 
                signal: abortCtrl.signal,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => renderResults(data, term))
            .catch(e => { if(e.name !== 'AbortError') console.error(e); });
        });

        // التعامل مع لوحة المفاتيح
        searchInput.addEventListener('keydown', (e) => {
            if (!resultsContainer.classList.contains('is-visible')) return;
            const items = resultsContainer.querySelectorAll('.search-item-row');
            let active = resultsContainer.querySelector('.search-item-row.active');
            let index = Array.from(items).indexOf(active);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (active) active.classList.remove('active');
                index = (index + 1) % items.length;
                if(items[index]) { items[index].classList.add('active'); items[index].scrollIntoView({block: 'nearest'}); }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (active) active.classList.remove('active');
                index = (index - 1 + items.length) % items.length;
                if(items[index]) { items[index].classList.add('active'); items[index].scrollIntoView({block: 'nearest'}); }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (active) active.click();
                else if (items[0]) items[0].click();
            } else if (e.key === 'Escape') {
                resultsContainer.classList.remove('is-visible');
            }
        });

        // إخفاء عند الضغط خارج
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.remove('is-visible');
            }
        });

        // دالة الرسم
        function renderResults(items, term) {
            resultsContainer.innerHTML = '';
            
            if (!items || items.length === 0) {
                const div = document.createElement('div');
                div.className = 'search-item-row text-center text-muted';
                div.innerHTML = `لا توجد نتائج. <a href="#" id="btn-create-item" class="text-primary fw-bold">إنشاء "${term}"؟</a>`;
                div.querySelector('#btn-create-item').onclick = (e) => {
                    e.preventDefault();
                    createNewItem(term);
                };
                resultsContainer.appendChild(div);
            } else {
                items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'search-item-row';
                    div.innerHTML = `<strong>${item.name}</strong>`;
                    div.onclick = () => addItem(item.id);
                    resultsContainer.appendChild(div);
                });
            }
            resultsContainer.classList.add('is-visible');
        }
    }

    function addItem(itemId) {
        const searchInput = document.getElementById('search-input');
        const resultsContainer = document.getElementById('invoice-search-results');
        
        // إعداد الحقول
        searchInput.value = '';
        resultsContainer.classList.remove('is-visible');

        // إرسال طلب الإضافة
        postData('/api/add-item', { item_id: itemId })
            .then(data => {
                if (data.success && data.html) {
                    appendRowToTable(data.html);
                    calculateInvoiceTotal();
                    
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success', 
                        title: data.message, showConfirmButton: false, timer: 1500
                    });
                    searchInput.focus();
                } else {
                    Swal.fire('خطأ', data.message || 'فشل في إضافة الصنف', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('خطأ', 'فشل في جلب بيانات الصنف', 'error');
            });
    }

    function createNewItem(name) {
        postData('/api/create-item', { name: name })
            .then(data => {
                if (data.success) {
                    addItem(data.item_id);
                }
            });
    }

    // -------------------------------------------------------------------------
    // 3. UI Helpers & Calculations
    // -------------------------------------------------------------------------
    
    function appendRowToTable(html) {
        // البحث عن tbody في جدول الأصناف باستخدام الكلاس المحدد له
        const tbody = document.querySelector('.invoice-items-table tbody');
        if (tbody) {
            tbody.insertAdjacentHTML('beforeend', html);
        }
    }

    // دوال الحسابات تلقائياً عند تغيير الكمية أو السعر
    window.calculateRow = function(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const totalCell = row.querySelector('.row-total');
        
        const total = (qty * price).toFixed(2);
        totalCell.innerText = total;

        calculateInvoiceTotal();
    };

    window.calculateInvoiceTotal = function() {
        let subtotal = 0;
        document.querySelectorAll('.row-total').forEach(cell => {
            subtotal += parseFloat(cell.innerText) || 0;
        });

        // تحديث المجموع الفرعي
        const subtotalDisplay = document.getElementById('invoice-subtotal');
        if (subtotalDisplay) subtotalDisplay.innerText = subtotal.toFixed(2);

        // حساب الخصومات والإضافات (جلب القيم من الحقول)
        const discPerc = parseFloat(document.getElementById('discount-percentage')?.value) || 0;
        const discVal = parseFloat(document.getElementById('discount-value')?.value) || 0;
        const addPerc = parseFloat(document.getElementById('additional-percentage')?.value) || 0;
        const addVal = parseFloat(document.getElementById('additional-value')?.value) || 0;

        // صافي الفاتورة المبدئي
        let net = subtotal;

        // تطبيق الخصم
        if (discPerc > 0) net -= (subtotal * (discPerc / 100));
        else net -= discVal;

        // تطبيق الإضافات
        if (addPerc > 0) net += (subtotal * (addPerc / 100));
        else net += addVal;

        // تحديث الصافي
        const netDisplay = document.getElementById('invoice-net-total');
        if (netDisplay) netDisplay.innerText = net.toFixed(2);

        // تحديث الحقل المخفي للإرسال
        const totalInput = document.getElementById('total_after_additional');
        if (totalInput) totalInput.value = net.toFixed(2);

        // حساب المتبقي
        const received = parseFloat(document.getElementById('received-from-client')?.value) || 0;
        const remaining = net - received;
        const remainingDisplay = document.getElementById('display-remaining');
        if (remainingDisplay) {
            remainingDisplay.innerText = remaining.toFixed(2);
            remainingDisplay.className = 'col-3 text-left font-weight-bold small ' + 
                                       (remaining > 0.01 ? 'text-danger' : (remaining < -0.01 ? 'text-success' : ''));
        }
    };

    // ربط مستمعات الأحداث لحقول الفوتر يدوياً لأنها لم تعد مراقبة بـ Livewire في الوقت الفعلي
    document.addEventListener('input', (e) => {
        if (['discount-percentage', 'discount-value', 'additional-percentage', 'additional-value', 'received-from-client'].includes(e.target.id)) {
            calculateInvoiceTotal();
        }
    });

    // -------------------------------------------------------------------------
    // 4. Init & Events
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', initSearch);
    
    // دعم التنقل بـ Livewire إذا كان المودول يعمل بـ Navigate
    if (typeof Livewire !== 'undefined') {
        document.addEventListener('livewire:navigated', initSearch);
    }
    
    // اختصار F2 للبحث السريع
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2') {
            e.preventDefault();
            document.getElementById('search-input')?.focus();
        }
    });

})();
</script>
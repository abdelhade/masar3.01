@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" id="filterDateFrom" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" id="filterDateTo" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">رقم القيد</label>
                        <input type="text" id="filterJournalId" class="form-control" placeholder="مثال: 1024">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">اسم الحساب</label>
                        <input type="text" id="filterAccount" class="form-control" placeholder="ابحث باسم الحساب">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">نوع العملية</label>
                        <select id="filterType" class="form-select">
                            <option value="">الكل</option>
                            @foreach ($operationTypes as $type)
                                <option value="{{ $type->ptext }}">{{ $type->ptext }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <label class="form-label">الحركة</label>
                        <select id="filterDC" class="form-select">
                            <option value="">الكل</option>
                            <option value="debit">مدين>0</option>
                            <option value="credit">دائن>0</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-bordered table-hover table-sm  mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">

                        <tr class="journal_tr text-center">
                            <th>م</th>
                            <th>رقم القيد</th>
                            <th>مدين</th>
                            <th>دائن</th>
                            <th>اسم الحساب</th>
                            <th>بيان</th>
                            <th>نوع العملية</th>
                            <th>التاريخ</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($journalHeads as $i => $head)
                            @foreach ($head->dets as $j => $detail)
                                <tr class="journal-row" data-journal-id="{{ $head->journal_id }}"
                                    data-type="{{ $head->oper?->type?->ptext ?? '' }}" data-date="{{ $head->date }}"
                                    data-accname="{{ $detail->accHead->aname ?? '' }}"
                                    data-debit="{{ (float) $detail->debit }}" data-credit="{{ (float) $detail->credit }}">
                                    @if ($j == 0)
                                        <td class="font-family-cairo fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $i + 1 }}</td>
                                        <td class="font-family-cairo fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->journal_id }}</td>
                                    @endif

                                    <td class="font-family-cairo fw-bold font-14 text-center">{{ $detail->debit }}</td>
                                    <td class="font-family-cairo fw-bold font-14 text-center">{{ $detail->credit }}</td>
                                    <td class="font-family-cairo fw-bold font-14 text-center">
                                        {{ $detail->accHead->aname ?? '-' }}</td>
                                    @if ($j == 0)
                                        <td class="font-family-cairo fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->details }}</td>
                                        <td class="font-family-cairo fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->oper?->type?->ptext ?? '-' }}
                                        </td>
                                        <td class="font-family-cairo fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->date }}</td>
                                    @endif
                                </tr>
                            @endforeach

                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info py-3 mb-0" style="font-size: 1.2rem; font-weight: 500;">
                                        <i class="las la-info-circle me-2"></i>
                                        لا توجد بيانات
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
            <div class="mt-2 small text-muted" id="rowsCount"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = () => Array.from(document.querySelectorAll('.journal-row'));
            const els = {
                from: document.getElementById('filterDateFrom'),
                to: document.getElementById('filterDateTo'),
                jid: document.getElementById('filterJournalId'),
                acc: document.getElementById('filterAccount'),
                type: document.getElementById('filterType'),
                dc: document.getElementById('filterDC'),
                cnt: document.getElementById('rowsCount')
            };

            // التحقق من وجود العناصر
            if (!els.from || !els.to || !els.jid || !els.acc || !els.type || !els.dc) {
                console.error('بعض عناصر الفلترة غير موجودة في DOM');
                return;
            }

            // تعيين تاريخ اليوم إذا كانت الحقول فارغة
            const today = new Date().toISOString().split('T')[0];
            if (!els.from.value) {
                els.from.value = today;
            }
            if (!els.to.value) {
                els.to.value = today;
            }

            function inRange(dateStr, fromStr, toStr) {
                if (!dateStr) return false;
                const d = new Date(dateStr);
                if (fromStr) {
                    const f = new Date(fromStr);
                    f.setHours(0, 0, 0, 0); // بداية اليوم
                    if (d < f) return false;
                }
                if (toStr) {
                    const t = new Date(toStr);
                    t.setHours(23, 59, 59, 999); // نهاية اليوم
                    if (d > t) return false;
                }
                return true;
            }

            function applyFilters() {
                const fFrom = els.from.value;
                const fTo = els.to.value;
                const fJid = els.jid.value.trim().toLowerCase();
                const fAcc = els.acc.value.trim().toLowerCase();
                const fType = els.type.value.trim().toLowerCase();
                const fDC = els.dc.value;
                let visible = 0;
                
                rows().forEach(tr => {
                    const jid = String(tr.getAttribute('data-journal-id') || '').toLowerCase();
                    const acc = String(tr.getAttribute('data-accname') || '').toLowerCase();
                    const typ = String(tr.getAttribute('data-type') || '').toLowerCase();
                    const dat = tr.getAttribute('data-date') || '';
                    const debit = parseFloat(tr.getAttribute('data-debit') || '0');
                    const credit = parseFloat(tr.getAttribute('data-credit') || '0');

                    let ok = true;
                    // تطبيق فلترة التاريخ (إلزامي إذا كان هناك قيمة)
                    if (fFrom || fTo) {
                        ok = ok && inRange(dat, fFrom, fTo);
                    }
                    if (fJid) ok = ok && jid.includes(fJid);
                    if (fAcc) ok = ok && acc.includes(fAcc);
                    // فلترة نوع العملية - مطابقة كاملة
                    if (fType) ok = ok && typ === fType;
                    if (fDC === 'debit') ok = ok && debit > 0;
                    if (fDC === 'credit') ok = ok && credit > 0;

                    tr.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                });
                
                if (els.cnt) {
                    els.cnt.textContent = `عدد الأسطر الظاهرة: ${visible}`;
                }
            }

            // جعل الدالة متاحة عالمياً للزر
            window.applyFilters = applyFilters;

            // إضافة مستمعي الأحداث
            // للحقول النصية: keyup و input
            ['keyup', 'input'].forEach(ev => {
                ['jid', 'acc'].forEach(k => {
                    if (els[k]) {
                        els[k].addEventListener(ev, applyFilters);
                    }
                });
            });
            // للحقول التاريخية والـ select: change فقط
            ['change'].forEach(ev => {
                ['from', 'to', 'type', 'dc'].forEach(k => {
                    if (els[k]) {
                        els[k].addEventListener(ev, applyFilters);
                    }
                });
            });
            
            // تطبيق الفلاتر عند التحميل لعرض نتائج اليوم
            applyFilters();
        });
    </script>
@endsection

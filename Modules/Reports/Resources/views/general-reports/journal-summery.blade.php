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
                        <label class="form-label">{{ __('From Date') }}</label>
                        <input type="date" id="filterDateFrom" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">{{ __('To Date') }}</label>
                        <input type="date" id="filterDateTo" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">{{ __('Entry Number') }}</label>
                        <input type="text" id="filterJournalId" class="form-control" placeholder="{{ __('e.g. 1024') }}">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('Account Name') }}</label>
                        <input type="text" id="filterAccount" class="form-control"
                            placeholder="{{ __('Search by account name') }}">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">{{ __('Operation Type') }}</label>
                        <input type="text" id="filterType" class="form-control"
                            placeholder="{{ __('Search by operation type') }}">
                    </div>
                    <div class="col-sm-1">
                        <label class="form-label">{{ __('Movement') }}</label>
                        <select id="filterDC" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="debit">{{ __('Debit > 0') }}</option>
                            <option value="credit">{{ __('Credit > 0') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-bordered table-hover table-sm mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">
                        <tr class="journal_tr text-center">
                            <th>#</th>
                            <th>{{ __('Entry Number') }}</th>
                            <th>{{ __('Debit') }}</th>
                            <th>{{ __('Credit') }}</th>
                            <th>{{ __('Account Name') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Operation Type') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($journalHeads as $i => $head)
                            @foreach ($head->dets as $j => $detail)
                                <tr class="journal-row" data-journal-id="{{ $head->journal_id }}"
                                    data-type="{{ $head->oper?->type?->ptext ?? '' }}" data-date="{{ $head->date }}"
                                    data-accname="{{ $detail->accHead->aname ?? '' }}"
                                    data-debit="{{ (float) $detail->debit }}" data-credit="{{ (float) $detail->credit }}">

                                    @if ($j === 0)
                                        <td class="font-hold fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $i + 1 }}</td>
                                        <td class="font-hold fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->journal_id }}</td>
                                    @endif

                                    <td class="font-hold fw-bold font-14 text-center">
                                        {{ number_format($detail->debit, 2) }}</td>
                                    <td class="font-hold fw-bold font-14 text-center">
                                        {{ number_format($detail->credit, 2) }}</td>
                                    <td class="font-hold fw-bold font-14 text-center">
                                        {{ $detail->accHead->aname ?? '-' }}
                                    </td>

                                    @if ($j === 0)
                                        <td class="font-hold fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->details ?? '-' }}</td>
                                        <td class="font-hold fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->oper?->type?->ptext ?? '-' }}
                                        </td>
                                        <td class="font-hold fw-bold font-14 text-center"
                                            rowspan="{{ $head->dets->count() }}">{{ $head->date }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info py-3 mb-0" style="font-size: 1.2rem; font-weight: 500;">
                                        <i class="las la-info-circle me-2"></i>
                                        {{ __('No data available') }}
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
        document.addEventListener('DOMContentLoaded', () => {
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

            // التحقق من وجود جميع العناصر
            if (Object.values(els).some(el => !el)) {
                console.error('بعض عناصر الفلترة غير موجودة في الصفحة');
                return;
            }

            // تعيين التاريخ الافتراضي إذا لم يكن موجود
            const today = new Date().toISOString().split('T')[0];
            els.from.value = els.from.value || today;
            els.to.value = els.to.value || today;

            function inRange(dateStr, fromStr, toStr) {
                if (!dateStr) return false;
                const d = new Date(dateStr);
                if (fromStr) {
                    const f = new Date(fromStr);
                    f.setHours(0, 0, 0, 0);
                    if (d < f) return false;
                }
                if (toStr) {
                    const t = new Date(toStr);
                    t.setHours(23, 59, 59, 999);
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
                    const jid = (tr.dataset.journalId || '').toLowerCase();
                    const acc = (tr.dataset.accname || '').toLowerCase();
                    const typ = (tr.dataset.type || '').toLowerCase();
                    const dat = tr.dataset.date || '';
                    const debit = parseFloat(tr.dataset.debit || '0');
                    const credit = parseFloat(tr.dataset.credit || '0');

                    let ok = true;

                    if (fFrom || fTo) {
                        ok = ok && inRange(dat, fFrom, fTo);
                    }
                    if (fJid) ok = ok && jid.includes(fJid);
                    if (fAcc) ok = ok && acc.includes(fAcc);
                    if (fType) ok = ok && typ.includes(fType);
                    if (fDC === 'debit') ok = ok && debit > 0;
                    if (fDC === 'credit') ok = ok && credit > 0;

                    tr.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                });

                els.cnt.textContent = `عدد الأسطر الظاهرة: ${visible}`;
            }

            // ربط الأحداث
            ['jid', 'acc', 'type'].forEach(key => {
                els[key].addEventListener('input', applyFilters);
                els[key].addEventListener('keyup', applyFilters);
            });

            ['from', 'to', 'dc'].forEach(key => {
                els[key].addEventListener('change', applyFilters);
            });

            // تطبيق الفلاتر مباشرة عند التحميل
            applyFilters();
        });
    </script>
@endsection

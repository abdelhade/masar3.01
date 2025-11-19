<?php

use Livewire\Volt\Component;

use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;
use App\Models\OperHead;
use App\Models\User;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $boxsAccounts = [];
    public $selectedBox = null;
    public $fromDate = null;
    public $toDate = null;
    public $operheads = [];


    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
        $this->boxsAccounts = AccHead::where('code', 'like', '%1101%')->where('is_basic', 0)->where('isdeleted', 0)->pluck('aname', 'id');
        $this->selectedBox = $this->boxsAccounts->first();
    }

    public function search()
    {
        $this->operheads = OperHead::whereHas('journalHead.dets', function ($query) {
            $query->where('account_id', $this->selectedBox);
        })
        ->when($this->fromDate, function ($query) {
            $query->whereDate('pro_date', '>=', $this->fromDate);
        })
        ->when($this->toDate, function ($query) {
            $query->whereDate('pro_date', '<=', $this->toDate);
        })
        ->with(['journalHead.dets' => function ($query) {
            $query->where('account_id', $this->selectedBox);
        }, 'type', 'user', 'acc1Head'])
        ->orderBy('pro_date', 'desc')
        ->get();
    }
}; ?>

<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">تقرير حركة الصندوق</h4>
        </div>
        <div class="card-body row">
            <div class="form-group col-md-3">
                <label for="box">الحساب</label>
                <select class="form-control" wire:model="selectedBox" id="box">
                    @foreach ($boxsAccounts as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="from_date">التاريخ الأول</label>
                <input type="date" class="form-control" wire:model="fromDate" id="from_date">
            </div>
            <div class="form-group col-md-3">
                <label for="to_date">التاريخ الأخير</label>
                <input type="date" class="form-control" wire:model="toDate" id="to_date">
            </div>
            <div class="form-group col-md-3 mt-4">
                <button class="btn btn-primary" wire:click="search">بحث</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>تاريخ العملية</th>
                            <th>تاريخ الإنشاء</th>
                            <th>رقم العملية</th>
                            <th>نوع العملية</th>
                            <th>مدين</th>
                            <th>دائن</th>
                            <th>الموظف</th>
                            <th>المستخدم</th>
                            <th>تاريخ السجل</th>
                            <th>ملاحظات</th>
                            <th>تم المراجعة</th>
                            <th class="text-end">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($operheads as $operhead)
                            @php
                                $journalDetails = $operhead->journalHead?->dets ?? collect();
                                $cashboxDetails = $journalDetails->where('account_id', $this->selectedBox);
                            @endphp
                            @if($cashboxDetails->isNotEmpty())
                                @foreach($cashboxDetails as $detail)
                                    <tr>
                                        <td>{{ $operhead->accural_date ?? $operhead->pro_date }}</td>
                                        <td>{{ $operhead->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $operhead->pro_id }}</td>
                                        <td>{{ $operhead->type?->ptext ?? $operhead->pro_type }}</td>
                                        <td>{{ number_format($detail->debit ?? 0, 2) }}</td>
                                        <td>{{ number_format($detail->credit ?? 0, 2) }}</td>
                                        <td>{{ AccHead::find($operhead->emp_id)?->aname ?? '-' }}
                                            {{-- @dd($operhead) --}}
                                        </td>
                                        <td>{{ User::find($operhead->user)?->name ?? '-' }}</td>
                                        <td>{{ $detail->crtime ?? $operhead->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $operhead->info }}</td>
                                        <td>{{ $operhead->closed ? __('نعم') : __('لا') }}</td>
                                        <td class="text-end">
                                            <a href="{{ $operhead->getViewUrl() }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>{{ $operhead->accural_date ?? $operhead->pro_date }}</td>
                                    <td>{{ $operhead->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $operhead->pro_num }}</td>
                                    <td>{{ $operhead->type?->ptext ?? $operhead->pro_type }}</td>
                                    <td colspan="8" class="text-center">{{ __('لا توجد تفاصيل') }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">{{ __('لا توجد بيانات') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
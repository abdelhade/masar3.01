<?php

use Livewire\Volt\Component;

use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;
use App\Models\OperHead;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $customers;

    public function mount()
    {
        $this->customers = AccHead::where('code', 'like', '1103%')->where('is_basic', 0)->where('isdeleted', 0)->get();
    }

    public function getCustomerDebtByAgeRange($customerId, $fromDays, $toDays = null)
    {
        $today = Carbon::now()->startOfDay();
        $startDate = $today->copy()->subDays($toDays ?? $fromDays);
        $endDate = $fromDays > 0 ? $today->copy()->subDays($fromDays - 1) : $today;

        // جلب مجموع الديون للفترة العمرية المحددة
        $debtSum = OperHead::where('acc1', $customerId)
            ->whereBetween('accural_date', [$startDate, $endDate])
            ->selectRaw('SUM(fat_net - paid_from_client) as debt_sum')
            ->value('debt_sum');

        return $debtSum ?? 0;
    }

    // دالة للحصول على الديون من 1 إلى 15 يوم
    public function getDebt1to15($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 1, 15);
    }

    // دالة للحصول على الديون من 16 إلى 30 يوم
    public function getDebt16to30($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 16, 30);
    }

    // دالة للحصول على الديون من 31 إلى 60 يوم
    public function getDebt31to60($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 31, 60);
    }

    // دالة للحصول على الديون من 61 إلى 90 يوم
    public function getDebt61to90($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 61, 90);
    }

    // دالة للحصول على الديون من 91 إلى 120 يوم
    public function getDebt91to120($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 91, 120);
    }

    // دالة للحصول على الديون أكثر من 120 يوم
    public function getDebtOver120($customerId)
    {
        $today = Carbon::now()->startOfDay();
        $startDate = $today->copy()->subDays(9999); // تاريخ قديم جداً كبداية للفترة
        $endDate = $today->copy()->subDays(121);

        $debtSum = OperHead::where('acc1', $customerId)
            ->whereBetween('accural_date', [$startDate, $endDate])
            ->selectRaw('SUM(fat_net - paid_from_client) as debt_sum')
            ->value('debt_sum');

        return $debtSum ?? 0;
    }
}; ?>

<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Customer Debt Aging Report') }}</h4>
        </div>

        <div class="card-body">
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center text-white">#</th>
                            <th class="text-white">{{ __('Customer Name') }}</th>
                            <th class="text-white text-end">{{ __('1-15 Days') }}</th>
                            <th class="text-white text-end">{{ __('16-30 Days') }}</th>
                            <th class="text-white text-end">{{ __('31-60 Days') }}</th>
                            <th class="text-white text-end">{{ __('61-90 Days') }}</th>
                            <th class="text-white text-end">{{ __('91-120 Days') }}</th>
                            <th class="text-white text-end">{{ __('Over 120 Days') }}</th>
                            <th class="text-center text-white">{{ __('Total Debt') }}</th>
                            <th class="text-center text-white">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->customers ?? collect() as $index => $customer)
                            @php
                                $debt1_15 = $this->getDebt1to15($customer->id);
                                $debt16_30 = $this->getDebt16to30($customer->id);
                                $debt31_60 = $this->getDebt31to60($customer->id);
                                $debt61_90 = $this->getDebt61to90($customer->id);
                                $debt91_120 = $this->getDebt91to120($customer->id);
                                $debtOver120 = $this->getDebtOver120($customer->id);
                                $totalDebt =
                                    $debt1_15 + $debt16_30 + $debt31_60 + $debt61_90 + $debt91_120 + $debtOver120;
                            @endphp
                            <tr class="{{ $totalDebt > 0 ? 'table-warning' : '' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $customer->aname ?? __('Unspecified') }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-success fw-bold">{{ number_format($debt1_15, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-info fw-bold">{{ number_format($debt16_30, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-warning fw-bold">{{ number_format($debt31_60, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-danger fw-bold">{{ number_format($debt61_90, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-danger fw-bold">{{ number_format($debt91_120, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-danger fw-bold">{{ number_format($debtOver120, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $totalDebt > 0 ? 'bg-warning' : 'bg-success' }} fs-6">
                                        {{ number_format($totalDebt, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-outline-info"
                                        title="{{ __('View Details') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        {{ __('No Customer Debt Data Available') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="2" class="text-end text-white fw-bold">{{ __('Total') }}</th>
                            <th class="text-end text-white fw-bold">
                                {{ number_format(array_sum(array_map(fn($c) => $this->getDebt1to15($c->id), $this->customers->toArray())), 2) }}
                            </th>
                            <th class="text-end text-white fw-bold">
                                {{ number_format(array_sum(array_map(fn($c) => $this->getDebt16to30($c->id), $this->customers->toArray())), 2) }}
                            </th>
                            <th class="text-end text-white fw-bold">
                                {{ number_format(array_sum(array_map(fn($c) => $this->getDebt31to60($c->id), $this->customers->toArray())), 2) }}
                            </th>
                            <th class="text-end text-white fw-bold">
                                {{ number_format(array_sum(array_map(fn($c) => $this->getDebt61to90($c->id), $this->customers->toArray())), 2) }}
                            </th>
                            <th class="text-end text-white fw-bold">
                                {{ number_format(array_sum(array_map(fn($c) => $this->getDebt91to120($c->id), $this->customers->toArray())), 2) }}
                            </th>
                            <th class="text-end text-white fw-bold">
                                {{ number_format(array_sum(array_map(fn($c) => $this->getDebtOver120($c->id), $this->customers->toArray())), 2) }}
                            </th>
                            <th class="text-end text-white fw-bold">
                                {{ number_format(
                                    array_sum(
                                        array_map(
                                            fn($c) => array_sum([
                                                $this->getDebt1to15($c->id),
                                                $this->getDebt16to30($c->id),
                                                $this->getDebt31to60($c->id),
                                                $this->getDebt61to90($c->id),
                                                $this->getDebt91to120($c->id),
                                                $this->getDebtOver120($c->id),
                                            ]),
                                            $this->customers->toArray(),
                                        ),
                                    ),
                                    2,
                                ) }}
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

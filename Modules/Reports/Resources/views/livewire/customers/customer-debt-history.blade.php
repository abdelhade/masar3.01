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
        
        // Ø¬Ù„Ø¨ Ø§Ù„Ø¯ÙŠÙˆÙ† Ù„Ù„ÙØªØ±Ø© Ø§Ù„Ø¹Ù…Ø±ÙŠØ© Ø§Ù„Ù…Ø­Ø¯Ø¯Ø©
        $debtSum = OperHead::where('acc1', $customerId)
            ->whereBetween('accural_date', [$startDate, $endDate])
            ->selectRaw('SUM(fat_net - paid_from_client) as debt_sum')
            ->value('debt_sum');
            
        return $debtSum ?? 0;
    }

    // Ø¯Ø§Ù„Ø© Ù„Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ø§Ù„Ø¯ÙŠÙˆÙ† Ù…Ù† 1 Ø¥Ù„Ù‰ 15 ÙŠÙˆÙ…
    public function getDebt1to15($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 1, 15);
    }

    // Ø¯Ø§Ù„Ø© Ù„Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ø§Ù„Ø¯ÙŠÙˆÙ† Ù…Ù† 16 Ø¥Ù„Ù‰ 30 ÙŠÙˆÙ…
    public function getDebt16to30($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 16, 30);
    }

    // Ø¯Ø§Ù„Ø© Ù„Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ø§Ù„Ø¯ÙŠÙˆÙ† Ù…Ù† 31 Ø¥Ù„Ù‰ 60 ÙŠÙˆÙ…
    public function getDebt31to60($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 31, 60);
    }

    // Ø¯Ø§Ù„Ø© Ù„Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ø§Ù„Ø¯ÙŠÙˆÙ† Ù…Ù† 61 Ø¥Ù„Ù‰ 90 ÙŠÙˆÙ…
    public function getDebt61to90($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 61, 90);
    }

    // Ø¯Ø§Ù„Ø© Ù„Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ø§Ù„Ø¯ÙŠÙˆÙ† Ù…Ù† 91 Ø¥Ù„Ù‰ 120 ÙŠÙˆÙ…
    public function getDebt91to120($customerId)
    {
        return $this->getCustomerDebtByAgeRange($customerId, 91, 120);
    }

    // Ø¯Ø§Ù„Ø© Ù„Ù„Ø­ØµÙˆÙ„ Ø¹Ù„Ù‰ Ø§Ù„Ø¯ÙŠÙˆÙ† Ø£ÙƒØ«Ø± Ù…Ù† 120 ÙŠÙˆÙ…
    public function getDebtOver120($customerId)
    {
        $today = Carbon::now()->startOfDay();
        $startDate = $today->copy()->subDays(9999); // ØªØ§Ø±ÙŠØ® Ù‚Ø¯ÙŠÙ… Ø¬Ø¯Ø§Ù‹
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
            <h4 class="card-title">{{ __('reports.customer_debt_history') }}</h4>
        </div>
        
        <div class="card-body">           
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-centered mb-0 overflow-auto">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-white">#</th>
                            <th class="text-white">{{ __('reports.customer_name') }}</th>
                            <th class="text-white">{{ __('reports.from_day_to_15_days') }}</th>
                            <th class="text-white">{{ __('reports.from_16_to_30_days') }}</th>
                            <th class="text-white">{{ __('reports.from_31_to_60_days') }}</th>
                            <th class="text-white">{{ __('reports.from_61_to_90_days') }}</th>
                            <th class="text-white">{{ __('reports.from_91_to_120_days') }}</th>
                            <th class="text-white">{{ __('reports.over_120_days') }}</th>
                            <th class="text-white">{{ __('reports.operations') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->customers ?? collect() as $index => $customer)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $customer->aname ?? __('reports.unspecified') }}</td>
                                <td>{{ number_format($this->getDebt1to15($customer->id), 2) }}</td>
                                <td>{{ number_format($this->getDebt16to30($customer->id), 2) }}</td>
                                <td>{{ number_format($this->getDebt31to60($customer->id), 2) }}</td>
                                <td>{{ number_format($this->getDebt61to90($customer->id), 2) }}</td>
                                <td>{{ number_format($this->getDebt91to120($customer->id), 2) }}</td>
                                <td>{{ number_format($this->getDebtOver120($customer->id), 2) }}</td>
                                <td>
                                    <a href="#">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> {{ __('reports.no_customer_data_for_period') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

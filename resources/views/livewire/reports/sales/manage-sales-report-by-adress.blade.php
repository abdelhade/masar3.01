 <?php

use Livewire\Volt\Component;

use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use App\Models\City;
use App\Models\State;
use App\Models\Town;
use App\Models\OperHead;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?string $fromDate = null;                
    public ?string $toDate = null;
    public $countries = [];
    public $cities = [];
    public $states = [];
    public $towns = [];
    public $salesByAddress = [];
    // Filter properties
    public $countryId = null;
    public $stateId = null;
    public $cityId = null;
    public $townId = null;

    public function mount(): void
    {
        $this->countries = Country::all()->pluck('title', 'id');
        $this->cities = City::all()->pluck('title', 'id');
        $this->states = State::all()->pluck('title', 'id');
        $this->towns = Town::all()->pluck('title', 'id');
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->endOfMonth()->toDateString();
        $this->loadSalesData();
    }

    // Update states and cities and towns when country changes
    public function updatedCountryId($value)
    {
        $this->stateId = null;
        $this->cityId = null;
        $this->townId = null;
        
        if ($value) {
            $this->states = State::where('country_id', $value)->pluck('title', 'id');
            $this->cities = City::where('state_id', $value)->pluck('title', 'id');
            $this->towns = Town::where('city_id', $value)->pluck('title', 'id');
            
        } else {
            $this->states = State::all()->pluck('title', 'id');
            $this->cities = City::all()->pluck('title', 'id');
            $this->towns = Town::all()->pluck('title', 'id');
        }
        
        $this->loadSalesData();
    }

    // Update cities and towns when state changes
    public function updatedStateId($value)
    {
        $this->cityId = null;
        $this->townId = null;
        
        if ($value) {
            $this->cities = City::where('state_id', $value)->pluck('title', 'id');
            $this->towns = Town::where('city_id', $value)->pluck('title', 'id');
        } else {
            $this->cities = City::all()->pluck('title', 'id');
            $this->towns = Town::all()->pluck('title', 'id');
        }
        
        $this->loadSalesData();
    }

    // Update towns when city changes
    public function updatedCityId($value)
    {
        $this->townId = null;
        
        if ($value) {
            $this->towns = Town::where('city_id', $value)->pluck('title', 'id');
        } else {
            $this->towns = Town::all()->pluck('title', 'id');
        }
        
        $this->loadSalesData();
    }

    // Update data when town changes
    public function updatedTownId($value)
    {
        $this->loadSalesData();
    }

    // Update data when date filters change
    public function updatedFromDate($value)
    {
        $this->loadSalesData();
    }

    public function updatedToDate($value)
    {
        $this->loadSalesData();
    }

    // Reset filters
    public function resetFilters()
    {
        $this->reset(['countryId', 'cityId', 'stateId', 'townId', 'fromDate', 'toDate']);
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->endOfMonth()->toDateString();
        $this->mount();
    }

    // Load sales data method
    public function loadSalesData()
    {
        try {
            $query = OperHead::query()
                ->where('pro_type', 10) // Sales Invoice
                ->with(['acc1Head.country', 'acc1Head.city', 'acc1Head.state', 'acc1Head.town', 'employee', 'acc1Head']);
                
            // Add date filters
            if (!empty($this->fromDate)) {
                $query->whereDate('pro_date', '>=', $this->fromDate);
            }
            if (!empty($this->toDate)) {
                $query->whereDate('pro_date', '<=', $this->toDate);
            }

            // Add location filters through acc1Head relationship
            if (!empty($this->countryId)) {
                $query->whereHas('acc1Head', function($q) {
                    $q->where('country_id', $this->countryId);
                });
            }
            if (!empty($this->cityId)) {
                $query->whereHas('acc1Head', function($q) {
                    $q->where('city_id', $this->cityId);
                });
            }
            if (!empty($this->stateId)) {
                $query->whereHas('acc1Head', function($q) {
                    $q->where('state_id', $this->stateId);
                });
            }
            if (!empty($this->townId)) {
                $query->whereHas('acc1Head', function($q) {
                    $q->where('town_id', $this->townId);
                });
            }

            $this->salesByAddress = $query->get();
            
        } catch (\Exception $e) {
            Log::error("Error loading sales data: " . $e->getMessage());
            $this->salesByAddress = collect();
        }
    }


    // Get total sales amount
    public function getTotalAmountProperty()
    {
        return collect($this->salesByAddress)->sum('fat_total');
    }

    // Get total sales count
    public function getTotalSalesProperty()
    {
        return collect($this->salesByAddress)->count();
    }

}; ?>

<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">تقرير المبيعات بالعنوان</h4>
        </div>
        
        <div class="card-body">
            <!-- Filters Section -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <label for="fromDate" class="form-label">من تاريخ</label>
                    <input type="date" 
                           class="form-control" 
                           id="fromDate" 
                           wire:model.live="fromDate">
                </div>
                
                <div class="col-md-2">
                    <label for="toDate" class="form-label">إلى تاريخ</label>
                    <input type="date" 
                           class="form-control" 
                           id="toDate" 
                           wire:model.live="toDate">
                </div>
                
                <div class="col-md-2">
                    <label for="countryId" class="form-label">الدولة</label>
                    <select class="form-select" 
                            id="countryId" 
                            wire:model.live="countryId">
                        <option value="">جميع الدول</option>
                        @foreach($countries as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="cityId" class="form-label">المدينة</label>
                    <select class="form-select" 
                            id="cityId" 
                            wire:model.live="cityId">
                        <option value="">جميع المدن</option>
                        @foreach($cities as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="stateId" class="form-label">المنطقة</label>
                    <select class="form-select" 
                            id="stateId" 
                            wire:model.live="stateId">
                        <option value="">جميع المناطق</option>
                        @foreach($states as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="townId" class="form-label">الحي</label>
                    <select class="form-select" 
                            id="townId" 
                            wire:model.live="townId">
                        <option value="">جميع الأحياء</option>
                        @foreach($towns as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" 
                            class="btn btn-secondary" 
                            wire:click="resetFilters">
                        <i class="fas fa-refresh"></i> إعادة تعيين
                    </button>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">إجمالي المبيعات</h5>
                            <h3 class="mb-0">{{ number_format($this->totalSales) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">إجمالي المبلغ</h5>
                            <h3 class="mb-0">{{ number_format($this->totalAmount, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-centered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-white">#</th>
                            <th class="text-white">تاريخ الفاتورة</th>
                            <th class="text-white">تاريخ الاستحقاق</th>
                            <th class="text-white">اسم العميل</th>
                            <th class="text-white">الموظف</th>
                            <th class="text-white">قيمة الفاتورة</th>
                            <th class="text-white">المدفوع من العميل</th>
                            <th class="text-white">الربح</th>
                            <th class="text-white">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->salesByAddress ?? collect() as $index => $sale)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $sale->pro_date ?? 'غير محدد' }}</td>
                                <td>{{ $sale->accural_date ?? 'غير محدد' }}</td>
                                <td>{{ $sale->acc1Head?->aname ?? 'غير محدد' }}</td>
                                <td>{{ $sale->employee->aname ?? 'غير محدد' }}</td>
                                <td>{{ $sale->fat_net ?? 'غير محدد' }}</td>
                                <td>{{ $sale->paid_from_client ?? 'غير محدد' }}</td>
                                <td>{{ $sale->profit ?? 'غير محدد' }}</td>
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
                                        <i class="fas fa-info-circle"></i> لا توجد بيانات مبيعات للفترة المحددة
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- No Results Message -->
            @if(($this->salesByAddress ?? collect())->isEmpty())
                <div class="text-center mt-4">
                    <i class="fas fa-chart-bar fa-3x text-muted"></i>
                    <p class="text-muted mt-2">لا توجد بيانات مبيعات متاحة للفلاتر المحددة</p>
                </div>
            @endif
        </div>
    </div>

</div>

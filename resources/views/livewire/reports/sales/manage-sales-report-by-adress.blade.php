 <?php

 use Livewire\Volt\Component;

 use Livewire\WithPagination;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\DB;
 use App\Models\OperHead;
 use Illuminate\Support\Facades\Log;

 use Modules\HR\Models\City;
 use Modules\HR\Models\Town;
 use Modules\HR\Models\State;
 use Modules\HR\Models\Country;

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
                 $query->whereHas('acc1Head', function ($q) {
                     $q->where('country_id', $this->countryId);
                 });
             }
             if (!empty($this->cityId)) {
                 $query->whereHas('acc1Head', function ($q) {
                     $q->where('city_id', $this->cityId);
                 });
             }
             if (!empty($this->stateId)) {
                 $query->whereHas('acc1Head', function ($q) {
                     $q->where('state_id', $this->stateId);
                 });
             }
             if (!empty($this->townId)) {
                 $query->whereHas('acc1Head', function ($q) {
                     $q->where('town_id', $this->townId);
                 });
             }

             $this->salesByAddress = $query->get();
         } catch (\Exception $e) {
             Log::error('Error loading sales data: ' . $e->getMessage());
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
             <h4 class="card-title">{{ __('Sales Report by Address') }}</h4>
         </div>

         <div class="card-body">
             <!-- Filters Section -->
             <div class="row mb-4">
                 <div class="col-md-2">
                     <label for="fromDate" class="form-label">{{ __('From Date') }}</label>
                     <input type="date" class="form-control" id="fromDate" wire:model.live="fromDate">
                 </div>

                 <div class="col-md-2">
                     <label for="toDate" class="form-label">{{ __('To Date') }}</label>
                     <input type="date" class="form-control" id="toDate" wire:model.live="toDate">
                 </div>

                 <div class="col-md-2">
                     <label for="countryId" class="form-label">{{ __('Country') }}</label>
                     <select class="form-select" id="countryId" wire:model.live="countryId">
                         <option value="">{{ __('All Countries') }}</option>
                         @foreach ($countries as $id => $title)
                             <option value="{{ $id }}">{{ $title }}</option>
                         @endforeach
                     </select>
                 </div>

                 <div class="col-md-2">
                     <label for="cityId" class="form-label">{{ __('City') }}</label>
                     <select class="form-select" id="cityId" wire:model.live="cityId">
                         <option value="">{{ __('All Cities') }}</option>
                         @foreach ($cities as $id => $title)
                             <option value="{{ $id }}">{{ $title }}</option>
                         @endforeach
                     </select>
                 </div>

                 <div class="col-md-2">
                     <label for="stateId" class="form-label">{{ __('Region') }}</label>
                     <select class="form-select" id="stateId" wire:model.live="stateId">
                         <option value="">{{ __('All Regions') }}</option>
                         @foreach ($states as $id => $title)
                             <option value="{{ $id }}">{{ $title }}</option>
                         @endforeach
                     </select>
                 </div>

                 <div class="col-md-2">
                     <label for="townId" class="form-label">{{ __('District') }}</label>
                     <select class="form-select" id="townId" wire:model.live="townId">
                         <option value="">{{ __('All Districts') }}</option>
                         @foreach ($towns as $id => $title)
                             <option value="{{ $id }}">{{ $title }}</option>
                         @endforeach
                     </select>
                 </div>
             </div>

             <!-- Action Buttons -->
             <div class="row mb-3">
                 <div class="col-12">
                     <button type="button" class="btn btn-secondary" wire:click="resetFilters">
                         <i class="fas fa-refresh"></i> {{ __('Reset') }}
                     </button>
                 </div>
             </div>

             <!-- Summary Cards -->
             <div class="row mb-4">
                 <div class="col-md-6">
                     <div class="card bg-primary text-white">
                         <div class="card-body">
                             <h5 class="card-title">{{ __('Total Sales Count') }}</h5>
                             <h3 class="mb-0">{{ number_format($this->totalSales) }}</h3>
                         </div>
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="card bg-success text-white">
                         <div class="card-body">
                             <h5 class="card-title">{{ __('Total Amount') }}</h5>
                             <h3 class="mb-0">{{ number_format($this->totalAmount, 2) }} {{ __('SAR') }}</h3>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- Results Table -->
             <div class="table-responsive">
                 <table class="table table-striped table-centered mb-0">
                     <thead>
                         <tr>
                             <th class="text-white">#</th>
                             <th class="text-white">{{ __('Invoice Date') }}</th>
                             <th class="text-white">{{ __('Due Date') }}</th>
                             <th class="text-white">{{ __('Customer Name') }}</th>
                             <th class="text-white">{{ __('Employee') }}</th>
                             <th class="text-white">{{ __('Invoice Value') }}</th>
                             <th class="text-white">{{ __('Paid by Client') }}</th>
                             <th class="text-white">{{ __('Profit') }}</th>
                             <th class="text-white">{{ __('Actions') }}</th>
                         </tr>
                     </thead>
                     <tbody>
                         @forelse($this->salesByAddress ?? collect() as $index => $sale)
                             <tr>
                                 <td>{{ $index + 1 }}</td>
                                 <td>{{ $sale->pro_date ?? __('Not Specified') }}</td>
                                 <td>{{ $sale->accural_date ?? __('Not Specified') }}</td>
                                 <td>{{ $sale->acc1Head?->aname ?? __('Not Specified') }}</td>
                                 <td>{{ $sale->employee->aname ?? __('Not Specified') }}</td>
                                 <td>{{ $sale->fat_net ?? __('Not Specified') }}</td>
                                 <td>{{ $sale->paid_from_client ?? __('Not Specified') }}</td>
                                 <td>{{ $sale->profit ?? __('Not Specified') }}</td>
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
                                         <i class="fas fa-info-circle"></i>
                                         {{ __('No sales data for the selected period') }}
                                     </div>
                                 </td>
                             </tr>
                         @endforelse
                     </tbody>
                 </table>
             </div>

             <!-- No Results Message -->
             @if (($this->salesByAddress ?? collect())->isEmpty())
                 <div class="text-center mt-4">
                     <i class="fas fa-chart-bar fa-3x text-muted"></i>
                     <p class="text-muted mt-2">{{ __('No sales data available for the selected filters') }}</p>
                 </div>
             @endif
         </div>
     </div>
 </div>

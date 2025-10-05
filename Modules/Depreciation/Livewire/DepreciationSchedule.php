<?php

namespace Modules\Depreciation\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Depreciation\Models\AccountAsset;
use Modules\Branches\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DepreciationSchedule extends Component
{
    use WithPagination;

    public $selectedAsset = null;
    public $selectedBranch = '';
    public $showModal = false;
    public $scheduleData = [];
    public $selectedAssets = [];
    public $selectAll = false;
    public $search = '';
    public $filterStatus = '';
    public $showBulkActions = false;
    public $bulkAction = '';

    public function render()
    {
        $assets = AccountAsset::with(['accHead', 'accHead.branch'])
            ->where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('accHead', function ($accQuery) {
                        $accQuery->where('aname', 'like', '%' . $this->search . '%');
                    })->orWhere('asset_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedBranch, function ($query) {
                $query->whereHas('accHead', function ($q) {
                    $q->where('branch_id', $this->selectedBranch);
                });
            })
            ->when($this->filterStatus, function ($query) {
                if ($this->filterStatus === 'fully_depreciated') {
                    $query->whereRaw('accumulated_depreciation >= (purchase_cost - COALESCE(salvage_value, 0))');
                } elseif ($this->filterStatus === 'partially_depreciated') {
                    $query->whereRaw('accumulated_depreciation > 0 AND accumulated_depreciation < (purchase_cost - COALESCE(salvage_value, 0))');
                } elseif ($this->filterStatus === 'not_depreciated') {
                    $query->where('accumulated_depreciation', 0);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $branches = Branch::orderBy('name')->get();

        return view('depreciation::livewire.depreciation-schedule', [
            'assets' => $assets,
            'branches' => $branches,
        ]);
    }

    public function viewSchedule($assetId)
    {
        $this->selectedAsset = AccountAsset::with('accHead')->findOrFail($assetId);
        $this->scheduleData = $this->calculateDepreciationSchedule($this->selectedAsset);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedAsset = null;
        $this->scheduleData = [];
    }

    private function calculateDepreciationSchedule(AccountAsset $asset)
    {
        $schedule = [];
        
        if (!$asset->useful_life_years || !$asset->purchase_cost) {
            return $schedule;
        }

        $startDate = $asset->depreciation_start_date ? Carbon::parse($asset->depreciation_start_date) : Carbon::parse($asset->purchase_date);
        $depreciableAmount = $asset->purchase_cost - ($asset->salvage_value ?? 0);
        $currentBookValue = $asset->purchase_cost;
        $accumulatedDepreciation = 0;

        for ($year = 1; $year <= $asset->useful_life_years; $year++) {
            $yearStartDate = $startDate->copy()->addYears($year - 1);
            $yearEndDate = $startDate->copy()->addYears($year)->subDay();
            
            $annualDepreciation = $this->calculateYearlyDepreciation(
                $asset, 
                $currentBookValue, 
                $accumulatedDepreciation, 
                $depreciableAmount, 
                $year
            );

            if ($annualDepreciation <= 0) {
                break; // No more depreciation
            }

            $accumulatedDepreciation += $annualDepreciation;
            $currentBookValue -= $annualDepreciation;

            // Ensure we don't depreciate below salvage value
            if ($accumulatedDepreciation > $depreciableAmount) {
                $annualDepreciation -= ($accumulatedDepreciation - $depreciableAmount);
                $accumulatedDepreciation = $depreciableAmount;
                $currentBookValue = $asset->salvage_value ?? 0;
            }

            $schedule[] = [
                'year' => $year,
                'start_date' => $yearStartDate->format('Y-m-d'),
                'end_date' => $yearEndDate->format('Y-m-d'),
                'beginning_book_value' => $currentBookValue + $annualDepreciation,
                'annual_depreciation' => $annualDepreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'ending_book_value' => $currentBookValue,
                'percentage' => $depreciableAmount > 0 ? ($annualDepreciation / $depreciableAmount) * 100 : 0,
            ];

            // Break if fully depreciated
            if ($accumulatedDepreciation >= $depreciableAmount) {
                break;
            }
        }

        return $schedule;
    }

    private function calculateYearlyDepreciation(AccountAsset $asset, float $currentBookValue, float $accumulatedDepreciation, float $depreciableAmount, int $year): float
    {
        switch ($asset->depreciation_method) {
            case 'straight_line':
                return $depreciableAmount / $asset->useful_life_years;

            case 'double_declining':
                $rate = 2 / $asset->useful_life_years;
                $depreciation = $currentBookValue * $rate;
                
                // Don't depreciate below salvage value
                $remainingDepreciable = $depreciableAmount - $accumulatedDepreciation;
                return min($depreciation, $remainingDepreciable);

            case 'sum_of_years':
                $sumOfYears = ($asset->useful_life_years * ($asset->useful_life_years + 1)) / 2;
                $remainingYears = $asset->useful_life_years - ($year - 1);
                return ($depreciableAmount * $remainingYears) / $sumOfYears;

            default:
                return $depreciableAmount / $asset->useful_life_years;
        }
    }

    public function updatingSelectedBranch()
    {
        $this->resetPage();
    }

    public function exportSchedule($assetId)
    {
        $asset = AccountAsset::with('accHead')->findOrFail($assetId);
        $schedule = $this->calculateDepreciationSchedule($asset);
        
        $filename = 'depreciation_schedule_' . ($asset->asset_name ?: $asset->accHead->aname) . '_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($schedule, $asset) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for proper Arabic display in Excel
            fwrite($file, "\xEF\xBB\xBF");
            
            // Header
            fputcsv($file, [
                'Asset Name', 'Year', 'Start Date', 'End Date', 
                'Beginning Book Value', 'Annual Depreciation', 
                'Accumulated Depreciation', 'Ending Book Value', 'Percentage'
            ]);
            
            foreach ($schedule as $row) {
                fputcsv($file, [
                    $asset->asset_name ?: $asset->accHead->aname,
                    $row['year'],
                    $row['start_date'],
                    $row['end_date'],
                    number_format($row['beginning_book_value'], 2),
                    number_format($row['annual_depreciation'], 2),
                    number_format($row['accumulated_depreciation'], 2),
                    number_format($row['ending_book_value'], 2),
                    number_format($row['percentage'], 2) . '%'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedAssets = $this->getVisibleAssetIds();
        } else {
            $this->selectedAssets = [];
        }
        $this->showBulkActions = !empty($this->selectedAssets);
    }

    public function updatedSelectedAssets()
    {
        $this->selectAll = count($this->selectedAssets) === count($this->getVisibleAssetIds());
        $this->showBulkActions = !empty($this->selectedAssets);
    }

    private function getVisibleAssetIds()
    {
        return AccountAsset::query()
            ->with(['accHead'])
            ->where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('accHead', function ($accQuery) {
                        $accQuery->where('aname', 'like', '%' . $this->search . '%');
                    })->orWhere('asset_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedBranch, function ($query) {
                $query->whereHas('accHead', function ($q) {
                    $q->where('branch_id', $this->selectedBranch);
                });
            })
            ->when($this->filterStatus, function ($query) {
                if ($this->filterStatus === 'fully_depreciated') {
                    $query->whereRaw('accumulated_depreciation >= (purchase_cost - COALESCE(salvage_value, 0))');
                } elseif ($this->filterStatus === 'partially_depreciated') {
                    $query->whereRaw('accumulated_depreciation > 0 AND accumulated_depreciation < (purchase_cost - COALESCE(salvage_value, 0))');
                } elseif ($this->filterStatus === 'not_depreciated') {
                    $query->where('accumulated_depreciation', 0);
                }
            })
            ->pluck('id')
            ->toArray();
    }

    public function bulkProcess()
    {
        if (empty($this->selectedAssets) || empty($this->bulkAction)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'يرجى اختيار أصل واحد على الأقل واختيار الإجراء المطلوب'
            ]);
            return;
        }

        try {
            $response = Http::post(route('depreciation.schedule.bulk-process'), [
                'asset_ids' => $this->selectedAssets,
                'action' => $this->bulkAction
            ]);

            $result = $response->json();

            if ($result['success']) {
                $this->dispatch('alert', [
                    'type' => 'success',
                    'message' => $result['message']
                ]);
                
                $this->selectedAssets = [];
                $this->selectAll = false;
                $this->showBulkActions = false;
                $this->bulkAction = '';
            } else {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء المعالجة المجمعة: ' . $e->getMessage()
            ]);
        }
    }

    public function generateScheduleForAsset($assetId)
    {
        try {
            $response = Http::post(route('depreciation.schedule.generate'), [
                'asset_id' => $assetId
            ]);

            $result = $response->json();

            if ($result['success']) {
                $this->selectedAsset = $result['asset'];
                $this->scheduleData = $result['schedule'];
                $this->showModal = true;
            } else {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء الجدولة: ' . $e->getMessage()
            ]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
}
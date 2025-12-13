<?php

namespace Modules\Depreciation\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Depreciation\Models\AccountAsset;
use Modules\Branches\Models\Branch;
use Modules\Accounts\Models\AccHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DepreciationSchedule extends Component
{
    use WithPagination;

    public $selectedAsset = null;
    public $selectedBranch = '';
    public $showModal = false;
    public $showJournalModal = false;
    public $showFreeJournalModal = false;
    public $scheduleData = [];
    public $journalPreview = [];
    public $selectedAssetForJournal = null;
    public $search = '';
    public $filterStatus = '';
    public $filterMethod = '';
    public $filterUsefulLife = '';
    public $filterJournalStatus = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    
    // Free Journal Entry Fields
    public $freeJournalDate = '';
    public $freeJournalDescription = '';
    public $freeJournalDebitAccount = '';
    public $freeJournalDebitAmount = 0;
    public $freeJournalCreditAccount = '';
    public $freeJournalCreditAmount = 0;
    public $freeJournalNotes = '';
    public $freeJournalAssetId = null;

    public function render()
    {
        $assets = AccountAsset::with([
                'accHead:id,aname,code,branch_id', 
                'accHead.branch:id,name',
                'depreciationAccount:id,aname,code',
                'expenseAccount:id,aname,code'
            ])
            ->select(['id', 'acc_head_id', 'asset_name', 'purchase_date', 'depreciation_start_date', 
                     'purchase_cost', 'salvage_value', 'useful_life_years', 'depreciation_method', 
                     'annual_depreciation', 'accumulated_depreciation', 'is_active', 'last_depreciation_date',
                     'depreciation_account_id', 'expense_account_id'])
            ->where('is_active', true)
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereHas('accHead', function ($accQuery) use ($searchTerm) {
                        $accQuery->where('aname', 'like', $searchTerm)
                                 ->orWhere('code', 'like', $searchTerm);
                    })->orWhere('asset_name', 'like', $searchTerm);
                });
            })
            ->when($this->selectedBranch, function ($query) {
                $query->whereHas('accHead', function ($q) {
                    $q->where('branch_id', $this->selectedBranch);
                });
            })
            ->when($this->filterMethod, function ($query) {
                $query->where('depreciation_method', $this->filterMethod);
            })
            ->when($this->filterUsefulLife, function ($query) {
                switch ($this->filterUsefulLife) {
                    case '1-5':
                        $query->whereBetween('useful_life_years', [1, 5]);
                        break;
                    case '6-10':
                        $query->whereBetween('useful_life_years', [6, 10]);
                        break;
                    case '11-20':
                        $query->whereBetween('useful_life_years', [11, 20]);
                        break;
                    case '21+':
                        $query->where('useful_life_years', '>', 20);
                        break;
                }
            })
            ->when($this->filterJournalStatus, function ($query) {
                if ($this->filterJournalStatus === 'has_journal') {
                    $query->whereNotNull('last_depreciation_date');
                } elseif ($this->filterJournalStatus === 'no_journal') {
                    $query->whereNull('last_depreciation_date');
                } elseif ($this->filterJournalStatus === 'current_month') {
                    $query->whereMonth('last_depreciation_date', now()->month)
                          ->whereYear('last_depreciation_date', now()->year);
                }
            })
            ->when($this->filterDateFrom, function ($query) {
                $query->where('depreciation_start_date', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function ($query) {
                $query->where('depreciation_start_date', '<=', $this->filterDateTo);
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
            ->paginate(15);

        $branches = Branch::select(['id', 'name'])->orderBy('name')->get();
        $accounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->select(['id', 'code', 'aname', 'branch_id'])
            ->orderBy('code')
            ->get();

        return view('depreciation::livewire.depreciation-schedule', [
            'assets' => $assets,
            'branches' => $branches,
            'accounts' => $accounts,
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

            case 'declining_balance':
                // Single declining balance with rate = 1 / useful life
                $rate = 1 / max($asset->useful_life_years, 1);
                $db = $currentBookValue * $rate;
                $remainingDepreciable = $depreciableAmount - $accumulatedDepreciation;
                return min($db, $remainingDepreciable);

            case 'double_declining':
                $rate = 2 / max($asset->useful_life_years, 1);
                $ddb = $currentBookValue * $rate;
                
                // Straight-line for remaining life
                $remainingDepreciable = $depreciableAmount - $accumulatedDepreciation;
                $remainingYears = max($asset->useful_life_years - ($year - 1), 1);
                $slRemaining = $remainingDepreciable / $remainingYears;

                // Use the larger of DDB and SL-remaining to avoid under-depreciation in later years
                $depreciation = max($ddb, $slRemaining);

                // Cap at remaining depreciable amount so we don't go below salvage
                return min($depreciation, $remainingDepreciable);

            case 'sum_of_years':
                $sumOfYears = ($asset->useful_life_years * ($asset->useful_life_years + 1)) / 2;
                $remainingYears = $asset->useful_life_years - ($year - 1);
                return ($depreciableAmount * $remainingYears) / $sumOfYears;

            default:
                return $depreciableAmount / $asset->useful_life_years;
        }
    }

    public function createDepreciationEntry($assetId)
    {
        try {
            $asset = AccountAsset::with(['accHead', 'depreciationAccount', 'expenseAccount'])->findOrFail($assetId);
            
            // Validation
            if (!$asset->annual_depreciation || $asset->annual_depreciation <= 0) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'لا يمكن إنشاء قيد إهلاك - مبلغ الإهلاك السنوي غير محدد أو صفر'
                ]);
                return;
            }

            if (!$asset->depreciationAccount || !$asset->expenseAccount) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'حسابات الإهلاك غير مكتملة - يرجى التأكد من وجود حساب مجمع الإهلاك وحساب مصروف الإهلاك'
                ]);
                return;
            }

            // Calculate months passed since last depreciation
            $now = Carbon::now();
            $lastDepreciationDate = $asset->last_depreciation_date 
                ? Carbon::parse($asset->last_depreciation_date)
                : ($asset->depreciation_start_date 
                    ? Carbon::parse($asset->depreciation_start_date)
                    : Carbon::parse($asset->purchase_date));
            
            // Check if there's any time passed
            if ($lastDepreciationDate->greaterThanOrEqualTo($now)) {
                $this->dispatch('alert', [
                    'type' => 'warning',
                    'message' => 'لا توجد مدة فائتة للإهلاك - آخر إهلاك في: ' . $lastDepreciationDate->format('Y-m-d')
                ]);
                return;
            }

            // Calculate months passed (at least 1 month)
            $monthsPassed = max(1, $lastDepreciationDate->diffInMonths($now));
            
            // Calculate monthly depreciation
            $monthlyDepreciation = $asset->annual_depreciation / 12;
            
            // Calculate total depreciation for the period
            $totalDepreciation = $monthlyDepreciation * $monthsPassed;
            
            // Check if exceeds remaining depreciable amount
            $remainingDepreciable = ($asset->purchase_cost - ($asset->salvage_value ?? 0)) - $asset->accumulated_depreciation;
            if ($totalDepreciation > $remainingDepreciable) {
                $totalDepreciation = $remainingDepreciable;
                $monthsPassed = ceil(($totalDepreciation / $monthlyDepreciation));
            }
            
            // Prepare journal preview
            $this->selectedAssetForJournal = $asset;
            $this->journalPreview = [
                'asset_name' => $asset->asset_name ?: $asset->accHead->aname,
                'amount' => $totalDepreciation,
                'months' => $monthsPassed,
                'from_date' => $lastDepreciationDate->format('Y-m-d'),
                'to_date' => $now->format('Y-m-d'),
                'date' => $now->format('Y-m-d'),
                'debit_account' => $asset->expenseAccount->aname . ' (' . $asset->expenseAccount->code . ')',
                'credit_account' => $asset->depreciationAccount->aname . ' (' . $asset->depreciationAccount->code . ')',
                'description' => 'قيد إهلاك - ' . ($asset->asset_name ?: $asset->accHead->aname) . ' - ' . $monthsPassed . ' ' . ($monthsPassed == 1 ? 'شهر' : 'أشهر') . ' (' . $lastDepreciationDate->format('Y/m') . ' - ' . $now->format('Y/m') . ')'
            ];
            
            $this->showJournalModal = true;
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmJournalEntry()
    {
        try {
            DB::beginTransaction();
            
            $asset = $this->selectedAssetForJournal;
            $amount = $this->journalPreview['amount'];
            
            // Create journal entry
            $result = $this->createDepreciationJournalEntry($asset, $amount);
            
            if ($result['success']) {
            // Update asset
            $asset->increment('accumulated_depreciation', $amount);
            // Update last depreciation date to now (end of the period)
            $asset->update(['last_depreciation_date' => now()]);
                
                DB::commit();
                
                $this->dispatch('alert', [
                    'type' => 'success',
                    'message' => 'تم إنشاء قيد الإهلاك بنجاح - رقم القيد: ' . $result['journal_id']
                ]);
                
                $this->closeJournalModal();
            } else {
                DB::rollBack();
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء القيد: ' . $e->getMessage()
            ]);
        }
    }

    public function closeJournalModal()
    {
        $this->showJournalModal = false;
        $this->selectedAssetForJournal = null;
        $this->journalPreview = [];
    }

    private function createDepreciationJournalEntry($asset, $amount)
    {
        try {
            // Get next operation ID
            $lastProId = \App\Models\OperHead::where('pro_type', 50)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            // Create operation header
            $oper = \App\Models\OperHead::create([
                'pro_id' => $newProId,
                'pro_date' => now()->format('Y-m-d'),
                'pro_type' => 50, // إهلاك أصل
                'acc1' => $asset->expense_account_id,
                'acc2' => $asset->depreciation_account_id,
                'pro_value' => $amount,
                'details' => 'قيد إهلاك شهري - ' . ($asset->asset_name ?: $asset->accHead->aname),
                'info' => 'قيد إهلاك تلقائي - ' . now()->format('Y/m'),
              
                'user' => Auth::id(),
                'branch_id' => $asset->accHead->branch_id,
                'isdeleted' => 0,
            ]);

            // Get next journal ID
            $lastJournalId = \App\Models\JournalHead::max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;

            // Create journal header
            $journalHead = \App\Models\JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $amount,
                'op_id' => $oper->id,
                'pro_type' => 50, // إهلاك أصل
                'date' => now()->format('Y-m-d'),
                'details' => 'قيد إهلاك شهري - ' . ($asset->asset_name ?: $asset->accHead->aname),
                'user' => Auth::id(),
                'branch_id' => $asset->accHead->branch_id,
            ]);

            // Debit: Expense Account (مصروف الإهلاك)
            \App\Models\JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $asset->expense_account_id,
                'debit' => $amount,
                'credit' => 0,
                'type' => 0, // مدين
                'info' => 'مصروف إهلاك - ' . ($asset->asset_name ?: $asset->accHead->aname),
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'branch_id' => $asset->accHead->branch_id,
            ]);

            // Credit: Accumulated Depreciation Account (مجمع الإهلاك)
            \App\Models\JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $asset->depreciation_account_id,
                'debit' => 0,
                'credit' => $amount,
                'type' => 1, // دائن
                'info' => 'مجمع إهلاك - ' . ($asset->asset_name ?: $asset->accHead->aname),
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'branch_id' => $asset->accHead->branch_id,
            ]);

            return [
                'success' => true,
                'journal_id' => $newJournalId,
                'operation_id' => $oper->id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء القيد المحاسبي: ' . $e->getMessage()
            ];
        }
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
            
            fwrite($file, "\xEF\xBB\xBF");
            
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

    public function updatingFilterMethod()
    {
        $this->resetPage();
    }

    public function updatingFilterUsefulLife()
    {
        $this->resetPage();
    }

    public function updatingFilterJournalStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function clearDateFilters()
    {
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function setDateRange($period)
    {
        switch ($period) {
            case 'this_month':
                $this->filterDateFrom = now()->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->filterDateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_3_months':
                $this->filterDateFrom = now()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->filterDateFrom = now()->startOfYear()->format('Y-m-d');
                $this->filterDateTo = now()->endOfYear()->format('Y-m-d');
                break;
            case 'last_year':
                $this->filterDateFrom = now()->subYear()->startOfYear()->format('Y-m-d');
                $this->filterDateTo = now()->subYear()->endOfYear()->format('Y-m-d');
                break;
            case 'next_month':
                $this->filterDateFrom = now()->addMonth()->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->addMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'next_3_months':
                $this->filterDateFrom = now()->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->addMonths(3)->endOfMonth()->format('Y-m-d');
                break;
        }
        $this->resetPage();
    }

    public function updatingSelectedBranch()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openFreeJournalModal()
    {
        $this->freeJournalDate = now()->format('Y-m-d');
        $this->freeJournalDescription = '';
        $this->freeJournalDebitAccount = '';
        $this->freeJournalDebitAmount = 0;
        $this->freeJournalCreditAccount = '';
        $this->freeJournalCreditAmount = 0;
        $this->freeJournalNotes = '';
        $this->freeJournalAssetId = null;
        $this->showFreeJournalModal = true;
    }

    public function openFreeJournalModalForAsset($assetId)
    {
        $asset = AccountAsset::with(['accHead', 'depreciationAccount', 'expenseAccount'])->findOrFail($assetId);
        
        // Validation
        if (!$asset->depreciationAccount || !$asset->expenseAccount) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حسابات الإهلاك غير مكتملة - يرجى التأكد من وجود حساب مجمع الإهلاك وحساب مصروف الإهلاك'
            ]);
            return;
        }

        $this->freeJournalDate = now()->format('Y-m-d');
        $this->freeJournalDescription = 'قيد إهلاك يدوي - ' . ($asset->asset_name ?: $asset->accHead->aname);
        $this->freeJournalDebitAccount = $asset->expense_account_id;
        $this->freeJournalDebitAmount = $asset->annual_depreciation ? ($asset->annual_depreciation / 12) : 0;
        $this->freeJournalCreditAccount = $asset->depreciation_account_id;
        $this->freeJournalCreditAmount = $this->freeJournalDebitAmount;
        $this->freeJournalNotes = 'قيد إهلاك يدوي للأصل: ' . ($asset->asset_name ?: $asset->accHead->aname);
        $this->freeJournalAssetId = $assetId;
        $this->showFreeJournalModal = true;
    }

    public function closeFreeJournalModal()
    {
        $this->showFreeJournalModal = false;
        $this->freeJournalDate = '';
        $this->freeJournalDescription = '';
        $this->freeJournalDebitAccount = '';
        $this->freeJournalDebitAmount = 0;
        $this->freeJournalCreditAccount = '';
        $this->freeJournalCreditAmount = 0;
        $this->freeJournalNotes = '';
        $this->freeJournalAssetId = null;
    }

    public function updatedFreeJournalDebitAmount()
    {
        $this->freeJournalCreditAmount = $this->freeJournalDebitAmount;
    }

    public function createFreeJournalEntry()
    {
        if (!$this->freeJournalAssetId) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'يجب تحديد الأصل أولاً'
            ]);
            return;
        }

        $asset = AccountAsset::with(['accHead', 'depreciationAccount', 'expenseAccount'])->findOrFail($this->freeJournalAssetId);

        $this->validate([
            'freeJournalDate' => 'required|date',
            'freeJournalDescription' => 'required|string|max:255',
            'freeJournalDebitAccount' => 'required|exists:acc_head,id',
            'freeJournalDebitAmount' => 'required|numeric|min:0.01',
            'freeJournalCreditAccount' => 'required|exists:acc_head,id',
            'freeJournalNotes' => 'nullable|string|max:500',
        ], [
            'freeJournalDate.required' => 'التاريخ مطلوب',
            'freeJournalDescription.required' => 'الوصف مطلوب',
            'freeJournalDebitAccount.required' => 'حساب مصروف الإهلاك مطلوب',
            'freeJournalDebitAmount.required' => 'مبلغ الإهلاك مطلوب',
            'freeJournalCreditAccount.required' => 'حساب مجمع الإهلاك مطلوب',
        ]);

        // Validate amount doesn't exceed remaining depreciable amount
        $remainingDepreciable = ($asset->purchase_cost - ($asset->salvage_value ?? 0)) - $asset->accumulated_depreciation;
        if ($this->freeJournalDebitAmount > $remainingDepreciable) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'مبلغ الإهلاك أكبر من المبلغ القابل للإهلاك المتبقي: ' . number_format($remainingDepreciable, 2)
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            $debitAccount = AccHead::findOrFail($this->freeJournalDebitAccount);
            $creditAccount = AccHead::findOrFail($this->freeJournalCreditAccount);
            $this->freeJournalCreditAmount = $this->freeJournalDebitAmount;

            // Get branch from asset
            $branchId = $asset->accHead->branch_id ?? 1;

            // Get next operation ID
            $lastProId = \App\Models\OperHead::where('pro_type', 50)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            // Create operation header
            $oper = \App\Models\OperHead::create([
                'pro_id' => $newProId,
                'pro_date' => $this->freeJournalDate,
                'pro_type' => 50, // إهلاك أصل
                'acc1' => $debitAccount->id,
                'acc2' => $creditAccount->id,
                'pro_value' => $this->freeJournalDebitAmount,
                'details' => $this->freeJournalDescription,
                'info' => $this->freeJournalNotes ?: 'قيد إهلاك يدوي - ' . ($asset->asset_name ?: $asset->accHead->aname),
                'user' => Auth::id(),
                'branch_id' => $branchId,
                'isdeleted' => 0,
            ]);

            // Get next journal ID
            $lastJournalId = \App\Models\JournalHead::max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;

            // Create journal header
            $journalHead = \App\Models\JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $this->freeJournalDebitAmount,
                'op_id' => $oper->id,
                'pro_type' => 50, // إهلاك أصل
                'date' => $this->freeJournalDate,
                'details' => $this->freeJournalDescription,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // Debit Entry (Expense Account)
            \App\Models\JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $debitAccount->id,
                'debit' => $this->freeJournalDebitAmount,
                'credit' => 0,
                'type' => 0, // مدين
                'info' => 'مصروف إهلاك يدوي - ' . ($asset->asset_name ?: $asset->accHead->aname) . ($this->freeJournalNotes ? ' - ' . $this->freeJournalNotes : ''),
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'branch_id' => $branchId,
            ]);

            // Credit Entry (Accumulated Depreciation Account)
            \App\Models\JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $creditAccount->id,
                'debit' => 0,
                'credit' => $this->freeJournalCreditAmount,
                'type' => 1, // دائن
                'info' => 'مجمع إهلاك يدوي - ' . ($asset->asset_name ?: $asset->accHead->aname) . ($this->freeJournalNotes ? ' - ' . $this->freeJournalNotes : ''),
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'branch_id' => $branchId,
            ]);

            // Update asset accumulated depreciation
            $asset->increment('accumulated_depreciation', $this->freeJournalDebitAmount);
            $asset->update(['last_depreciation_date' => $this->freeJournalDate]);

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'تم إنشاء قيد الإهلاك بنجاح - رقم القيد: ' . $newJournalId . ' - الإهلاك المتراكم الجديد: ' . number_format($asset->fresh()->accumulated_depreciation, 2)
            ]);

            $this->closeFreeJournalModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء القيد: ' . $e->getMessage()
            ]);
        }
    }
}
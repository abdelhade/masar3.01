<?php

namespace Modules\Depreciation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Modules\Depreciation\Models\DepreciationItem;
use Illuminate\Support\Facades\DB;

class DepreciationController extends Controller
{
    public function index()
    {
        return view('depreciation::index');
    }

    /**
     * Show depreciation schedule
     */
    public function schedule()
    {
        return view('depreciation::schedule');
    }

    /**
     * Calculate depreciation for all active assets
     */
    public function calculateAllDepreciation()
    {
        try {
            DB::beginTransaction();
            
            $items = DepreciationItem::where('is_active', true)->get();
            $updatedCount = 0;
            
            foreach ($items as $item) {
                $yearsUsed = now()->diffInYears($item->purchase_date);
                $totalDepreciation = min(
                    $yearsUsed * $item->annual_depreciation, 
                    $item->cost - $item->salvage_value
                );
                
                $item->update([
                    'accumulated_depreciation' => $totalDepreciation
                ]);
                
                $updatedCount++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "تم حساب الإهلاك لـ {$updatedCount} أصل بنجاح",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب الإهلاك: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate depreciation report
     */
    public function report(Request $request)
    {
        $query = DepreciationItem::with(['assetAccount', 'branch']);
        
        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }
        
        if ($request->has('from_date') && $request->from_date) {
            $query->where('purchase_date', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->where('purchase_date', '<=', $request->to_date);
        }
        
        $items = $query->orderBy('purchase_date', 'desc')->get();
        $branches = Branch::orderBy('name')->get();
        
        return view('depreciation::report', compact('items', 'branches'));
    }

    /**
     * Sync asset depreciation accounts
     * This method ensures depreciation accounts are properly linked
     */
    public function syncDepreciationAccounts()
    {
        try {
            DB::beginTransaction();
            
            $items = DepreciationItem::whereNotNull('asset_account_id')
                ->where(function($query) {
                    $query->whereNull('depreciation_account_id')
                          ->orWhereNull('expense_account_id');
                })
                ->get();
            
            $syncedCount = 0;
            
            foreach ($items as $item) {
                // Find depreciation account (acc_type = 15)
                $depreciationAccount = AccHead::where('account_id', $item->asset_account_id)
                    ->where('acc_type', 15)
                    ->first();
                    
                // Find expense account (acc_type = 16)
                $expenseAccount = AccHead::where('account_id', $item->asset_account_id)
                    ->where('acc_type', 16)
                    ->first();
                
                if ($depreciationAccount || $expenseAccount) {
                    $item->update([
                        'depreciation_account_id' => $depreciationAccount?->id,
                        'expense_account_id' => $expenseAccount?->id,
                    ]);
                    $syncedCount++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "تم ربط {$syncedCount} حساب إهلاك بنجاح",
                'synced_count' => $syncedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء ربط حسابات الإهلاك: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate depreciation schedule for a specific asset
     */
    public function generateSchedule(Request $request)
    {
        try {
            $assetId = $request->input('asset_id');
            $asset = \Modules\Depreciation\Models\AccountAsset::with('accHead')->findOrFail($assetId);
            
            $schedule = $this->calculateDepreciationSchedule($asset);
            
            return response()->json([
                'success' => true,
                'schedule' => $schedule,
                'asset' => $asset
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الجدولة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export depreciation schedule to CSV
     */
    public function exportSchedule($assetId)
    {
        try {
            $asset = \Modules\Depreciation\Models\AccountAsset::with('accHead')->findOrFail($assetId);
            $schedule = $this->calculateDepreciationSchedule($asset);
            
            $filename = 'depreciation_schedule_' . ($asset->asset_name ?: $asset->accHead->aname) . '_' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($schedule, $asset) {
                $file = fopen('php://output', 'w');
                
                // UTF-8 BOM for proper Arabic display in Excel
                fwrite($file, "\xEF\xBB\xBF");
                
                // Header
                fputcsv($file, [
                    'اسم الأصل', 'السنة', 'من تاريخ', 'إلى تاريخ', 
                    'القيمة الدفترية في البداية', 'إهلاك السنة', 
                    'الإهلاك المتراكم', 'القيمة الدفترية في النهاية', 'النسبة %'
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
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تصدير الجدولة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk process depreciation schedule
     */
    public function bulkProcessSchedule(Request $request)
    {
        try {
            $assetIds = $request->input('asset_ids', []);
            $action = $request->input('action', 'calculate');
            
            if (empty($assetIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'يرجى اختيار أصل واحد على الأقل'
                ], 400);
            }
            
            DB::beginTransaction();
            
            $processedCount = 0;
            $totalAmount = 0;
            $errors = [];
            
            foreach ($assetIds as $assetId) {
                try {
                    $asset = \Modules\Depreciation\Models\AccountAsset::with('accHead')->find($assetId);
                    
                    if (!$asset || !$asset->is_active) {
                        continue;
                    }
                    
                    if ($action === 'calculate') {
                        // Calculate monthly depreciation
                        $monthlyDepreciation = $asset->annual_depreciation / 12;
                        $asset->increment('accumulated_depreciation', $monthlyDepreciation);
                        $asset->update(['last_depreciation_date' => now()]);
                        $totalAmount += $monthlyDepreciation;
                    } elseif ($action === 'reset') {
                        // Reset accumulated depreciation
                        $asset->update([
                            'accumulated_depreciation' => 0,
                            'last_depreciation_date' => null
                        ]);
                    }
                    
                    $processedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "خطأ في الأصل {$assetId}: " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "تم معالجة {$processedCount} أصل بنجاح",
                'processed_count' => $processedCount,
                'total_amount' => $totalAmount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة المجمعة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate depreciation schedule for an asset
     */
    private function calculateDepreciationSchedule($asset)
    {
        $schedule = [];
        
        if (!$asset->useful_life_years || !$asset->purchase_cost) {
            return $schedule;
        }

        $startDate = $asset->depreciation_start_date ? \Carbon\Carbon::parse($asset->depreciation_start_date) : \Carbon\Carbon::parse($asset->purchase_date);
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

    /**
     * Calculate yearly depreciation based on method
     */
    private function calculateYearlyDepreciation($asset, float $currentBookValue, float $accumulatedDepreciation, float $depreciableAmount, int $year): float
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
}

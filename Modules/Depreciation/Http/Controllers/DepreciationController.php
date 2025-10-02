<?php

namespace Modules\Depreciation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\AccHead;
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
}

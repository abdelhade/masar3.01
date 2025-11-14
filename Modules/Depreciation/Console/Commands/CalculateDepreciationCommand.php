<?php

namespace Modules\Depreciation\Console\Commands;

use Illuminate\Console\Command;
use Modules\Depreciation\Models\AccountAsset;
use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalculateDepreciationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'depreciation:calculate 
                            {--monthly : Calculate monthly depreciation} 
                            {--yearly : Calculate yearly depreciation}
                            {--asset= : Calculate for specific asset ID}
                            {--dry-run : Show what would be calculated without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Calculate and process depreciation for assets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting depreciation calculation...');

        $query = AccountAsset::query()
            ->where('is_active', true)
            ->whereNotNull('depreciation_start_date')
            ->where('depreciation_start_date', '<=', now());

        // Filter by specific asset if provided
        if ($this->option('asset')) {
            $query->where('id', $this->option('asset'));
        }

        $assets = $query->get();

        if ($assets->isEmpty()) {
            $this->warn('No assets found for depreciation calculation.');
            return 0;
        }

        $this->info(sprintf('Found %d assets for depreciation calculation', $assets->count()));

        $processedCount = 0;
        $totalAmount = 0;

        foreach ($assets as $asset) {
            if ($this->shouldCalculateDepreciation($asset)) {
                $depreciationAmount = $this->calculateDepreciationAmount($asset);
                
                if ($depreciationAmount > 0) {
                    $this->info(sprintf(
                        'Asset: %s - Depreciation: %s',
                        $asset->asset_name ?: $asset->accHead->aname,
                        number_format($depreciationAmount, 2)
                    ));

                    if (!$this->option('dry-run')) {
                        $this->processDepreciationEntry($asset, $depreciationAmount);
                    }

                    $processedCount++;
                    $totalAmount += $depreciationAmount;
                }
            }
        }

        if ($this->option('dry-run')) {
            $this->info('DRY RUN - No changes were made to the database.');
        }

        $this->info(sprintf(
            'Depreciation calculation completed. Processed %d assets with total amount: %s',
            $processedCount,
            number_format($totalAmount, 2)
        ));

        return 0;
    }

    /**
     * Determine if depreciation should be calculated for the asset
     */
    private function shouldCalculateDepreciation(AccountAsset $asset): bool
    {
        // Check if asset is fully depreciated
        if ($asset->isFullyDepreciated()) {
            return false;
        }

        $now = now();
        $lastDepreciationDate = $asset->last_depreciation_date 
            ? Carbon::parse($asset->last_depreciation_date) 
            : Carbon::parse($asset->depreciation_start_date);

        if ($this->option('monthly')) {
            // Calculate monthly if at least a month has passed
            return $lastDepreciationDate->diffInMonths($now) >= 1;
        } elseif ($this->option('yearly')) {
            // Calculate yearly if at least a year has passed
            return $lastDepreciationDate->diffInYears($now) >= 1;
        } else {
            // Default: calculate if at least a month has passed
            return $lastDepreciationDate->diffInMonths($now) >= 1;
        }
    }

    /**
     * Calculate the depreciation amount for the asset
     */
    private function calculateDepreciationAmount(AccountAsset $asset): float
    {
        if ($this->option('monthly')) {
            // Monthly depreciation
            return ($asset->annual_depreciation ?? 0) / 12;
        } elseif ($this->option('yearly')) {
            // Yearly depreciation
            return $asset->annual_depreciation ?? 0;
        } else {
            // Default: calculate based on months passed
            $now = now();
            $lastDepreciationDate = $asset->last_depreciation_date 
                ? Carbon::parse($asset->last_depreciation_date) 
                : Carbon::parse($asset->depreciation_start_date);

            $monthsPassed = $lastDepreciationDate->diffInMonths($now);
            $monthlyDepreciation = ($asset->annual_depreciation ?? 0) / 12;
            
            return $monthlyDepreciation * $monthsPassed;
        }
    }

    /**
     * Process the depreciation entry
     */
    private function processDepreciationEntry(AccountAsset $asset, float $amount): void
    {
        try {
            DB::beginTransaction();

            // Get or create depreciation accounts
            $depreciationAccounts = $this->getOrCreateDepreciationAccounts($asset->accHead);

            // Create depreciation voucher entry
            $this->createDepreciationVoucher(
                $asset->accHead,
                $depreciationAccounts['accumulated_depreciation'],
                $depreciationAccounts['expense_depreciation'],
                $amount
            );

            // Update asset record
            $asset->increment('accumulated_depreciation', $amount);
            $asset->update([
                'last_depreciation_date' => now(),
                'depreciation_account_id' => $depreciationAccounts['accumulated_depreciation']->id,
                'expense_account_id' => $depreciationAccounts['expense_depreciation']->id,
            ]);

            DB::commit();

            $this->info(sprintf('✓ Processed depreciation for: %s', $asset->asset_name ?: $asset->accHead->aname));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error(sprintf('✗ Error processing %s: %s', $asset->asset_name ?: $asset->accHead->aname, $e->getMessage()));
        }
    }

    private function getOrCreateDepreciationAccounts(AccHead $assetAccount)
    {
        // Find accumulated depreciation account (acc_type = 15)
        $accumulatedDepreciation = AccHead::where('account_id', $assetAccount->id)
            ->where('acc_type', 15)
            ->first();

        if (!$accumulatedDepreciation) {
            $accumulatedDepreciation = AccHead::create([
                'aname' => 'مجمع إهلاك ' . $assetAccount->aname,
                'code' => $this->generateAccountCode(15, $assetAccount->branch_id),
                'acc_type' => 15,
                'account_id' => $assetAccount->id,
                'branch_id' => $assetAccount->branch_id,
                'is_basic' => 0,
                'isdeleted' => 0,
            ]);
        }

        // Find expense depreciation account (acc_type = 16)
        $expenseDepreciation = AccHead::where('account_id', $assetAccount->id)
            ->where('acc_type', 16)
            ->first();

        if (!$expenseDepreciation) {
            $expenseDepreciation = AccHead::create([
                'aname' => 'مصروف إهلاك ' . $assetAccount->aname,
                'code' => $this->generateAccountCode(16, $assetAccount->branch_id),
                'acc_type' => 16,
                'account_id' => $assetAccount->id,
                'branch_id' => $assetAccount->branch_id,
                'is_basic' => 0,
                'isdeleted' => 0,
            ]);
        }

        return [
            'accumulated_depreciation' => $accumulatedDepreciation,
            'expense_depreciation' => $expenseDepreciation,
        ];
    }

    private function createDepreciationVoucher(AccHead $assetAccount, AccHead $accumulatedAccount, AccHead $expenseAccount, $amount)
    {
        // Get next operation ID
        $lastProId = OperHead::where('pro_type', 61)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        // Create operation header
        $oper = OperHead::create([
            'pro_id' => $newProId,
            'pro_date' => now()->format('Y-m-d'),
            'pro_type' => 61, // قيد يومية
            'acc1' => $expenseAccount->id,
            'acc2' => $accumulatedAccount->id,
            'pro_value' => $amount,
            'details' => 'قيد إهلاك تلقائي - ' . $assetAccount->aname,
            'info' => 'قيد إهلاك تلقائي من الأمر ' . $this->signature,
            'user' => 1, // System user
            'branch_id' => $assetAccount->branch_id,
            'isdeleted' => 0,
        ]);

        // Get next journal ID
        $lastJournalId = JournalHead::max('journal_id') ?? 0;
        $newJournalId = $lastJournalId + 1;

        // Create journal header
        $journalHead = JournalHead::create([
            'journal_id' => $newJournalId,
            'total' => $amount,
            'op_id' => $oper->id,
            'pro_type' => 61,
            'date' => now()->format('Y-m-d'),
            'details' => 'قيد إهلاك تلقائي - ' . $assetAccount->aname,
            'user' => 1,
            'branch_id' => $assetAccount->branch_id,
        ]);

        // Debit: Expense Account (مصروف الإهلاك)
        JournalDetail::create([
            'journal_id' => $newJournalId,
            'account_id' => $expenseAccount->id,
            'debit' => $amount,
            'credit' => 0,
            'type' => 0, // مدين
            'info' => 'مصروف إهلاك تلقائي - ' . $assetAccount->aname,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'branch_id' => $assetAccount->branch_id,
        ]);

        // Credit: Accumulated Depreciation Account (مجمع الإهلاك)
        JournalDetail::create([
            'journal_id' => $newJournalId,
            'account_id' => $accumulatedAccount->id,
            'debit' => 0,
            'credit' => $amount,
            'type' => 1, // دائن
            'info' => 'مجمع إهلاك تلقائي - ' . $assetAccount->aname,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'branch_id' => $assetAccount->branch_id,
        ]);

        return $oper;
    }

    private function generateAccountCode($accType, $branchId)
    {
        // Generate a unique account code based on account type and branch
        $prefix = $accType . str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $lastAccount = AccHead::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();
        
        if ($lastAccount) {
            $lastNumber = (int)substr($lastAccount->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
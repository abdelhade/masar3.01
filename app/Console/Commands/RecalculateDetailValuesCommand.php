<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\OperHead;
use App\Models\OperationItems;
use App\Services\Invoice\DetailValueCalculator;
use App\Services\Invoice\DetailValueValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RecalculateDetailValuesCommand
 *
 * Recalculates detail_value for operation items in historical invoices.
 * This command fixes invoices where detail_value was not calculated correctly,
 * ensuring that all discounts and additional charges are properly distributed.
 *
 * Usage:
 * - Fix all invoices: php artisan recalculation:fix-detail-values --all
 * - Fix specific invoice: php artisan recalculation:fix-detail-values --invoice-id=123
 * - Fix date range: php artisan recalculation:fix-detail-values --from-date=2024-01-01 --to-date=2024-12-31
 * - Dry run: php artisan recalculation:fix-detail-values --all --dry-run
 *
 * @package App\Console\Commands
 */
class RecalculateDetailValuesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculation:fix-detail-values
                            {--invoice-id= : Specific invoice ID to fix}
                            {--from-date= : Fix invoices from this date (YYYY-MM-DD)}
                            {--to-date= : Fix invoices until this date (YYYY-MM-DD)}
                            {--operation-type= : Fix specific operation type (11=purchase, 12=sales, etc.)}
                            {--all : Fix all invoices}
                            {--dry-run : Preview changes without saving}
                            {--batch-size=100 : Number of invoices per batch}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate detail_value for operation items with proper discount and additional handling';

    /**
     * Execute the console command.
     *
     * @param  DetailValueCalculator  $calculator  Service for calculating detail values
     * @return int Command exit code (0 = success, 1 = failure)
     */
    public function handle(DetailValueCalculator $calculator): int
    {
        $this->info('Starting detail_value recalculation...');
        $this->newLine();

        // Validate options
        if (! $this->validateOptions()) {
            return self::FAILURE;
        }

        try {
            // Build query for invoices to process
            $query = $this->buildInvoiceQuery();

            // Get total count
            $totalCount = $query->count();

            if ($totalCount === 0) {
                $this->info('No invoices found matching the criteria.');

                return self::SUCCESS;
            }

            $this->info("Found {$totalCount} invoices to process");
            $this->newLine();

            // Get options
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');
            $batchSize = (int) $this->option('batch-size');

            // Confirmation prompt (unless --force or --dry-run is used)
            if (! $dryRun && ! $force) {
                if (! $this->confirm("Do you want to recalculate detail_value for {$totalCount} invoices?", false)) {
                    $this->info('Operation cancelled');

                    return self::SUCCESS;
                }
                $this->newLine();
            }

            if ($dryRun) {
                $this->warn('DRY RUN MODE: No changes will be saved');
                $this->newLine();
            }

            // Process invoices in batches
            $processed = 0;
            $fixed = 0;
            $errors = 0;
            $skipped = 0;

            $progressBar = $this->output->createProgressBar($totalCount);
            $progressBar->start();

            $query->chunk($batchSize, function ($invoices) use (
                $calculator,
                $dryRun,
                &$processed,
                &$fixed,
                &$errors,
                &$skipped,
                $progressBar
            ) {
                foreach ($invoices as $invoice) {
                    try {
                        $result = $this->recalculateInvoice($invoice, $calculator, $dryRun);
                        $processed++;
                        $fixed += $result['fixed'];
                        $skipped += $result['skipped'];
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error('Error recalculating invoice', [
                            'invoice_id' => $invoice->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $progressBar->advance();
                }
            });

            $progressBar->finish();
            $this->newLine(2);

            // Display summary
            $this->displaySummary($processed, $fixed, $skipped, $errors, $dryRun);

            return $errors > 0 ? self::FAILURE : self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Fatal error: '.$e->getMessage());
            Log::error('Fatal error in RecalculateDetailValuesCommand', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Validate command options.
     *
     * @return bool True if options are valid, false otherwise
     */
    private function validateOptions(): bool
    {
        $invoiceId = $this->option('invoice-id');
        $fromDate = $this->option('from-date');
        $toDate = $this->option('to-date');
        $operationType = $this->option('operation-type');
        $all = $this->option('all');

        // Must specify at least one filter or --all
        if (! $all && ! $invoiceId && ! $fromDate && ! $toDate && ! $operationType) {
            $this->error('Please specify at least one filter option or use --all');
            $this->info('Available options: --invoice-id, --from-date, --to-date, --operation-type, --all');

            return false;
        }

        // Validate date format
        if ($fromDate && ! $this->isValidDate($fromDate)) {
            $this->error('Invalid --from-date format. Use YYYY-MM-DD');

            return false;
        }

        if ($toDate && ! $this->isValidDate($toDate)) {
            $this->error('Invalid --to-date format. Use YYYY-MM-DD');

            return false;
        }

        // Validate date range
        if ($fromDate && $toDate && $fromDate > $toDate) {
            $this->error('--from-date cannot be after --to-date');

            return false;
        }

        // Validate batch size
        $batchSize = (int) $this->option('batch-size');
        if ($batchSize < 1 || $batchSize > 1000) {
            $this->error('--batch-size must be between 1 and 1000');

            return false;
        }

        return true;
    }

    /**
     * Check if a date string is valid.
     *
     * @param  string  $date  Date string to validate
     * @return bool True if valid YYYY-MM-DD format
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Build query for invoices to process based on command options.
     *
     * @return \Illuminate\Database\Eloquent\Builder Query builder for OperHead
     */
    private function buildInvoiceQuery()
    {
        $query = OperHead::query()->with('operationItems');

        // Filter by invoice ID
        if ($invoiceId = $this->option('invoice-id')) {
            $query->where('id', $invoiceId);
        }

        // Filter by date range
        if ($fromDate = $this->option('from-date')) {
            $query->where('date', '>=', $fromDate);
        }

        if ($toDate = $this->option('to-date')) {
            $query->where('date', '<=', $toDate);
        }

        // Filter by operation type
        if ($operationType = $this->option('operation-type')) {
            $query->where('pro_type', $operationType);
        }

        // Order by date and ID for consistent processing
        $query->orderBy('date')->orderBy('id');

        return $query;
    }

    /**
     * Recalculate detail_value for a single invoice.
     *
     * @param  OperHead  $invoice  Invoice to recalculate
     * @param  DetailValueCalculator  $calculator  Calculator service
     * @param  bool  $dryRun  If true, don't save changes
     * @return array Results with 'fixed' and 'skipped' counts
     */
    private function recalculateInvoice(
        OperHead $invoice,
        DetailValueCalculator $calculator,
        bool $dryRun
    ): array {
        $fixed = 0;
        $skipped = 0;

        // Get all items for this invoice
        $items = $invoice->operationItems;

        if ($items->isEmpty()) {
            return ['fixed' => 0, 'skipped' => 0];
        }

        // Prepare items data for calculator
        $itemsData = $items->map(function ($item) {
            return [
                'item_price' => $item->item_price,
                'quantity' => $item->qty_in > 0 ? $item->qty_in : $item->qty_out,
                'item_discount' => $item->item_discount ?? 0,
                'additional' => $item->additional ?? 0,
            ];
        })->toArray();

        // Calculate invoice subtotal
        // Note: Using default 'invoice_level' mode for backward compatibility
        // TODO: Add discount_mode support when implementing settings
        try {
            $invoiceSubtotal = $calculator->calculateInvoiceSubtotal($itemsData, 'invoice_level');
        } catch (\Exception $e) {
            Log::warning('Could not calculate invoice subtotal', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return ['fixed' => 0, 'skipped' => $items->count()];
        }

        // Prepare invoice data
        $invoiceData = [
            'fat_disc' => $invoice->fat_disc ?? 0,
            'fat_disc_per' => $invoice->fat_disc_per ?? 0,
            'fat_plus' => $invoice->fat_plus ?? 0,
            'fat_plus_per' => $invoice->fat_plus_per ?? 0,
        ];

        // Recalculate each item
        foreach ($items as $index => $item) {
            try {
                // Calculate new detail_value
                $calculation = $calculator->calculate(
                    $itemsData[$index],
                    $invoiceData,
                    $invoiceSubtotal
                );

                $newDetailValue = $calculation['detail_value'];
                $oldDetailValue = $item->detail_value;

                // Check if value changed (with tolerance)
                $difference = abs($newDetailValue - $oldDetailValue);
                if ($difference < 0.01) {
                    $skipped++;

                    continue;
                }

                // Update item if not dry run
                if (! $dryRun) {
                    DB::transaction(function () use ($item, $newDetailValue) {
                        $item->update(['detail_value' => $newDetailValue]);
                    });
                }

                $fixed++;

                // Log the change
                Log::info('Detail value recalculated', [
                    'invoice_id' => $invoice->id,
                    'item_id' => $item->id,
                    'old_value' => $oldDetailValue,
                    'new_value' => $newDetailValue,
                    'difference' => $difference,
                    'dry_run' => $dryRun,
                ]);

            } catch (\Exception $e) {
                Log::error('Error recalculating item', [
                    'invoice_id' => $invoice->id,
                    'item_id' => $item->id,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        return ['fixed' => $fixed, 'skipped' => $skipped];
    }

    /**
     * Display summary of recalculation results.
     *
     * @param  int  $processed  Number of invoices processed
     * @param  int  $fixed  Number of items fixed
     * @param  int  $skipped  Number of items skipped
     * @param  int  $errors  Number of errors
     * @param  bool  $dryRun  Whether this was a dry run
     */
    private function displaySummary(
        int $processed,
        int $fixed,
        int $skipped,
        int $errors,
        bool $dryRun
    ): void {
        $this->info('=== Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Invoices Processed', $processed],
                ['Items Fixed', $fixed],
                ['Items Skipped (no change)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN: No changes were saved to the database');
            $this->info("Run without --dry-run to apply changes");
        } elseif ($fixed > 0) {
            $this->newLine();
            $this->info("✓ Successfully fixed {$fixed} items");
        }

        if ($errors > 0) {
            $this->newLine();
            $this->error("⚠ {$errors} errors occurred. Check logs for details.");
        }
    }
}

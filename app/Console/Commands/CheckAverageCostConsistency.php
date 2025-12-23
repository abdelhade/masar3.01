<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Consistency\AverageCostConsistencyChecker;
use Illuminate\Console\Command;

class CheckAverageCostConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculation:check-consistency
                            {--items=* : Specific item IDs to check (optional)}
                            {--all : Check all items}
                            {--auto-fix : Automatically fix inconsistencies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check average cost consistency for items';

    /**
     * Execute the console command.
     */
    public function handle(AverageCostConsistencyChecker $checker): int
    {
        $this->info('Starting average cost consistency check...');
        $this->newLine();

        $itemIds = $this->option('items');
        $checkAll = $this->option('all');
        $autoFix = $this->option('auto-fix');

        // Validate options
        if (! $checkAll && empty($itemIds)) {
            $this->error('Please specify either --all or --items option');

            return self::FAILURE;
        }

        if ($checkAll && ! empty($itemIds)) {
            $this->error('Cannot use both --all and --items options together');

            return self::FAILURE;
        }

        // Convert item IDs to integers
        if (! empty($itemIds)) {
            $itemIds = array_map('intval', $itemIds);
        }

        try {
            // Check consistency
            if ($checkAll) {
                $this->info('Checking all items...');
                $summary = $checker->checkAllItems();
                $inconsistencies = $summary['inconsistencies'];
                $totalItems = $summary['total_items'];
                $inconsistenciesFound = $summary['inconsistencies_found'];
            } else {
                $this->info('Checking '.count($itemIds).' items...');
                $inconsistencies = $checker->checkItems($itemIds);
                $totalItems = count($itemIds);
                $inconsistenciesFound = count($inconsistencies);
            }

            // Display results
            if (empty($inconsistencies)) {
                $this->info('✓ All items are consistent!');

                return self::SUCCESS;
            }

            $this->warn("Found {$inconsistenciesFound} inconsistencies out of {$totalItems} items checked");
            $this->newLine();

            // Display inconsistencies in table format
            $this->table(
                ['Item ID', 'Stored Average', 'Calculated Average', 'Difference', 'Total Qty', 'Total Value'],
                array_map(function ($item) {
                    return [
                        $item['item_id'],
                        number_format($item['stored_average'], 2),
                        number_format($item['calculated_average'], 2),
                        number_format($item['difference'], 2),
                        number_format($item['total_qty'], 2),
                        number_format($item['total_value'], 2),
                    ];
                }, $inconsistencies)
            );

            // Auto-fix if requested
            if ($autoFix) {
                $this->newLine();
                $this->warn('Auto-fixing inconsistencies...');

                $itemIdsToFix = array_column($inconsistencies, 'item_id');
                $result = $checker->fixInconsistencies($itemIdsToFix, false);

                $this->info("✓ Fixed {$result['items_fixed']} items");

                return self::SUCCESS;
            }

            $this->newLine();
            $this->info('To fix these inconsistencies, run:');
            $this->line('  php artisan recalculation:fix-inconsistencies');

            return self::FAILURE;

        } catch (\Exception $e) {
            $this->error('Error checking consistency: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

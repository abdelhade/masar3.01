<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Invoices\Services\Consistency\AverageCostConsistencyChecker;

class FixAverageCostInconsistencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculation:fix-inconsistencies
                            {--items=* : Specific item IDs to fix (optional)}
                            {--all : Fix all inconsistent items}
                            {--dry-run : Show what would be fixed without making changes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix average cost inconsistencies for items';

    /**
     * Execute the console command.
     */
    public function handle(AverageCostConsistencyChecker $checker): int
    {
        $this->info('Starting average cost inconsistency fix...');
        $this->newLine();

        $itemIds = $this->option('items');
        $fixAll = $this->option('all');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Validate options
        if (! $fixAll && empty($itemIds)) {
            $this->error('Please specify either --all or --items option');

            return self::FAILURE;
        }

        if ($fixAll && ! empty($itemIds)) {
            $this->error('Cannot use both --all and --items options together');

            return self::FAILURE;
        }

        try {
            // First, check for inconsistencies
            if ($fixAll) {
                $this->info('Checking all items for inconsistencies...');
                $summary = $checker->checkAllItems();
                $inconsistencies = $summary['inconsistencies'];
            } else {
                $itemIds = array_map('intval', $itemIds);
                $this->info('Checking '.count($itemIds).' items for inconsistencies...');
                $inconsistencies = $checker->checkItems($itemIds);
            }

            if (empty($inconsistencies)) {
                $this->info('✓ No inconsistencies found!');

                return self::SUCCESS;
            }

            $inconsistencyCount = count($inconsistencies);
            $this->warn("Found {$inconsistencyCount} inconsistencies");
            $this->newLine();

            // Display inconsistencies
            $this->table(
                ['Item ID', 'Stored Average', 'Calculated Average', 'Difference'],
                array_map(function ($item) {
                    return [
                        $item['item_id'],
                        number_format($item['stored_average'], 2),
                        number_format($item['calculated_average'], 2),
                        number_format($item['difference'], 2),
                    ];
                }, $inconsistencies)
            );

            // Dry run mode
            if ($dryRun) {
                $this->newLine();
                $this->info('DRY RUN MODE: No changes will be made');
                $this->info("Would fix {$inconsistencyCount} items");

                return self::SUCCESS;
            }

            // Confirmation prompt (unless --force is used)
            if (! $force) {
                $this->newLine();
                if (! $this->confirm("Do you want to fix these {$inconsistencyCount} inconsistencies?", false)) {
                    $this->info('Operation cancelled');

                    return self::SUCCESS;
                }
            }

            // Fix inconsistencies
            $this->newLine();
            $this->info('Fixing inconsistencies...');

            $itemIdsToFix = array_column($inconsistencies, 'item_id');
            $result = $checker->fixInconsistencies($itemIdsToFix, false);

            $this->newLine();
            $this->info("✓ Successfully fixed {$result['items_fixed']} items");

            // Display fixed items
            if (! empty($result['fixed_items'])) {
                $this->newLine();
                $this->table(
                    ['Item ID', 'Old Average', 'New Average', 'Difference'],
                    array_map(function ($item) {
                        return [
                            $item['item_id'],
                            number_format($item['old_average'], 2),
                            number_format($item['new_average'], 2),
                            number_format($item['difference'], 2),
                        ];
                    }, $result['fixed_items'])
                );
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error fixing inconsistencies: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

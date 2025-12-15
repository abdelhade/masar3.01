<?php

namespace Modules\Checks\Console\Commands;

use Illuminate\Console\Command;
use Modules\Checks\Events\CheckOverdue;
use Modules\Checks\Models\Check;

class CheckOverdueReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'checks:check-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Check for overdue checks and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $overdueChecks = Check::where('status', Check::STATUS_PENDING)
            ->where('due_date', '<', now()->toDateString())
            ->get();

        $count = 0;

        foreach ($overdueChecks as $check) {
            event(new CheckOverdue($check));
            $count++;
        }

        if ($count > 0) {
            $this->info("Found {$count} overdue checks");
        } else {
            $this->info('No overdue checks found');
        }

        return Command::SUCCESS;
    }
}

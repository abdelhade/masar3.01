<?php

namespace App\Console\Commands;

use App\Models\UserLocationTracking;
use Illuminate\Console\Command;

class CleanOldLocationTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:clean-old {--days=30 : Number of days to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old location tracking records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $this->info("Cleaning location tracking records older than {$days} days...");
        
        $deletedCount = UserLocationTracking::where('tracked_at', '<', $cutoffDate)->delete();
        
        $this->info("Deleted {$deletedCount} old location tracking records.");
        
        return self::SUCCESS;
    }
}

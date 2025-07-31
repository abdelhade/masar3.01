<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAttendanceData extends Command
{
    protected $signature = 'check:attendance-data';
    protected $description = 'Check raw attendance data in database';

    public function handle()
    {
        $this->info("Checking raw attendance data...");
        
        $records = DB::table('attendances')
            ->select('id', 'employee_id', 'date', 'time', 'type')
            ->orderBy('date')
            ->orderBy('time')
            ->limit(10)
            ->get();
        
        foreach ($records as $record) {
            $this->line("ID: {$record->id}, Employee: {$record->employee_id}, Date: {$record->date}, Time: {$record->time}, Type: {$record->type}");
        }
        
        return 0;
    }
} 
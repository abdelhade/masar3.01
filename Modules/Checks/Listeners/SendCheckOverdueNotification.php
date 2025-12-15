<?php

namespace Modules\Checks\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Checks\Events\CheckOverdue;

class SendCheckOverdueNotification
{
    /**
     * Handle the event.
     */
    public function handle(CheckOverdue $event): void
    {
        $check = $event->check;

        Log::warning('Check overdue', [
            'check_id' => $check->id,
            'check_number' => $check->check_number,
            'amount' => $check->amount,
            'due_date' => $check->due_date,
        ]);

        // يمكن إضافة إشعارات للمستخدمين هنا
    }
}

<?php

namespace Modules\Checks\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Checks\Events\CheckCreated;

class SendCheckCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(CheckCreated $event): void
    {
        $check = $event->check;

        Log::info('Check created', [
            'check_id' => $check->id,
            'check_number' => $check->check_number,
            'amount' => $check->amount,
            'type' => $check->type,
        ]);

        // يمكن إضافة إشعارات هنا
    }
}

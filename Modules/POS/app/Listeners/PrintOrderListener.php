<?php

declare(strict_types=1);

namespace Modules\POS\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\POS\Events\TransactionSaved;
use Modules\POS\Jobs\PrintKitchenOrderJob;
use Modules\POS\Services\KitchenPrinterService;

class PrintOrderListener
{
    public function __construct(
        private KitchenPrinterService $printerService
    ) {}

    public function handle(TransactionSaved $event): void
    {
        try {
            // تحديد محطات الطابعات المطلوبة بناءً على أصناف المعاملة
            $printerStations = $this->printerService
                ->determinePrinterStations($event->transaction);

            // إرسال مهمة طباعة لكل محطة
            foreach ($printerStations as $station) {
                PrintKitchenOrderJob::dispatch(
                    $event->transaction,
                    $station
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to queue kitchen print jobs', [
                'transaction_id' => $event->transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

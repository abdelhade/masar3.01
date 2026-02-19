<?php

declare(strict_types=1);

namespace Modules\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\POS\Models\CashierTransaction;
use Modules\POS\Models\KitchenPrinterStation;
use Modules\POS\Models\PrintJob;
use Modules\POS\Services\PrintContentFormatter;

class PrintKitchenOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public CashierTransaction $transaction,
        public KitchenPrinterStation $printerStation,
        public bool $isManual = false,
        public ?int $printedBy = null
    ) {
        $this->tries = config('kitchen-printer.max_retries', 3);
        $this->timeout = config('kitchen-printer.timeout', 10);
        $this->backoff = config('kitchen-printer.retry_backoff', 5);
    }

    /**
     * Execute the job.
     */
    public function handle(PrintContentFormatter $formatter): void
    {
        // Create print job record before printing
        $printJob = PrintJob::create([
            'printer_station_id' => $this->printerStation->id,
            'transaction_id' => $this->transaction->id,
            'content' => '',
            'status' => 'pending',
            'is_manual' => $this->isManual,
            'printed_by' => $this->printedBy,
            'attempts' => 0,
        ]);

        try {
            // Format print content
            $content = $formatter->format(
                $this->transaction,
                $this->printerStation
            );

            // Update content in print job
            $printJob->update(['content' => $content]);

            // Send HTTP POST request to print agent
            $printAgentUrl = config('kitchen-printer.print_agent_url', 'http://localhost:5000/print');
            $timeout = config('kitchen-printer.timeout', 5);

            $response = Http::timeout($timeout)
                ->post($printAgentUrl, [
                    'printer' => $this->printerStation->printer_name,
                    'content' => $content,
                ]);

            // Handle successful response
            if ($response->successful()) {
                $printJob->markAsSuccess();
            } else {
                // Handle HTTP error responses
                $errorMessage = "HTTP {$response->status()}: {$response->body()}";
                $printJob->markAsFailed($errorMessage);

                Log::warning('Kitchen print failed', [
                    'print_job_id' => $printJob->id,
                    'transaction_id' => $this->transaction->id,
                    'printer_station_id' => $this->printerStation->id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);

                // Retry only for server errors (5xx), not client errors (4xx)
                if ($response->serverError()) {
                    throw new \Exception($errorMessage);
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle network errors (connection refused, timeout, etc.)
            $errorMessage = 'فشل الاتصال بوكيل الطباعة: '.$e->getMessage();
            $printJob->markAsFailed($errorMessage);

            Log::error('Kitchen print connection exception', [
                'print_job_id' => $printJob->id,
                'transaction_id' => $this->transaction->id,
                'printer_station_id' => $this->printerStation->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger automatic retry
            throw $e;
        } catch (\Exception $e) {
            // Handle formatting errors and other exceptions
            $errorMessage = 'خطأ في معالجة الطباعة: '.$e->getMessage();
            $printJob->markAsFailed($errorMessage);

            Log::error('Kitchen print exception', [
                'print_job_id' => $printJob->id,
                'transaction_id' => $this->transaction->id,
                'printer_station_id' => $this->printerStation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger automatic retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Kitchen print job failed permanently', [
            'transaction_id' => $this->transaction->id,
            'printer_station_id' => $this->printerStation->id,
            'printer_name' => $this->printerStation->printer_name,
            'is_manual' => $this->isManual,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optionally, notify administrators about permanent failure
        // This could be implemented using Laravel notifications
    }
}

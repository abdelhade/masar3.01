<?php

declare(strict_types=1);

namespace Modules\CRM\Jobs;

use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\CampaignLog;
use Modules\CRM\Services\CampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public CampaignLog $log,
        public Campaign $campaign
    ) {}

    public function handle(CampaignService $service): void
    {
        try {
            $client = $this->log->client;

            if (!$client || !$client->email) {
                $this->log->markAsFailed('العميل أو البريد الإلكتروني غير موجود');
                return;
            }

            // استبدال المتغيرات
            $message = $service->replaceVariables($this->campaign->message, $client);
            $subject = $service->replaceVariables($this->campaign->subject, $client);

            // إضافة روابط التتبع
            $message = $service->addTrackingLinks($message, $this->campaign);

            // إضافة pixel التتبع
            $message = $service->addTrackingPixel($message, $this->log->tracking_code);

            // إرسال البريد
            Mail::html($message, function ($mail) use ($client, $subject) {
                $mail->to($client->email)
                    ->subject($subject);
            });

            // تحديث الحالة
            $this->log->markAsSent();

            // تحديث إحصائيات الحملة
            $service->updateCampaignStats($this->campaign);

        } catch (\Exception $e) {
            Log::error('Campaign email failed', [
                'campaign_id' => $this->campaign->id,
                'log_id' => $this->log->id,
                'error' => $e->getMessage(),
            ]);

            $this->log->markAsFailed($e->getMessage());
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->log->markAsFailed($exception->getMessage());
    }
}

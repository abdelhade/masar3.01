<?php

declare(strict_types=1);

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CRM\Models\CampaignLog;
use Modules\CRM\Models\CampaignLink;
use Modules\CRM\Services\CampaignService;

class CampaignTrackingController extends Controller
{
    public function __construct(
        private CampaignService $campaignService
    ) {}

    /**
     * تتبع فتح الإيميل (Tracking Pixel)
     */
    public function trackOpen(string $trackingCode)
    {
        $log = CampaignLog::where('tracking_code', $trackingCode)->first();

        if ($log && $log->status !== 'opened' && $log->status !== 'clicked') {
            $log->markAsOpened();
            $this->campaignService->updateCampaignStats($log->campaign);
        }

        // إرجاع صورة شفافة 1x1 pixel
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * تتبع النقر على الروابط
     */
    public function trackClick(string $trackingCode)
    {
        $link = CampaignLink::where('tracking_code', $trackingCode)->first();

        if (!$link) {
            abort(404);
        }

        // زيادة عدد النقرات
        $link->incrementClicks();

        // تحديث إحصائيات الحملة
        $this->campaignService->updateCampaignStats($link->campaign);

        // إعادة التوجيه للرابط الأصلي
        return redirect($link->original_url);
    }
}

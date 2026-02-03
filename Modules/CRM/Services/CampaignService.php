<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Models\Client;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\CampaignLog;
use Modules\CRM\Models\CampaignLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CampaignService
{
    /**
     * جلب العملاء المستهدفين حسب الفلاتر
     */
    public function getTargetedCustomers(array $filters): Collection
    {
        $query = Client::query();

        // فلتر العنوان (بدلاً من المدينة)
        if (!empty($filters['address'])) {
            $query->where('address', 'like', '%' . $filters['address'] . '%');
        }

        // فلتر نوع العميل
        if (!empty($filters['client_type_id'])) {
            $query->where('client_type_id', $filters['client_type_id']);
        }

        // فلتر تصنيف العميل
        if (!empty($filters['client_category_id'])) {
            $query->where('client_category_id', $filters['client_category_id']);
        }

        // فلتر آخر شراء (بالأيام)
        if (!empty($filters['last_purchase_days'])) {
            $days = (int) $filters['last_purchase_days'];
            $query->whereHas('invoices', function ($q) use ($days) {
                $q->where('created_at', '>=', now()->subDays($days));
            });
        }

        // فلتر إجمالي المشتريات (أكبر من مبلغ معين)
        if (!empty($filters['total_purchases_min'])) {
            $amount = (float) $filters['total_purchases_min'];
            $query->whereRaw('(
                SELECT COALESCE(SUM(pro_value), 0)
                FROM operhead 
                WHERE operhead.acc2 = clients.id 
                AND operhead.pro_type = 1
            ) >= ?', [$amount]);
        }

        // فلتر حالة العميل (نشط/غير نشط)
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // فلتر العملاء اللي عندهم إيميل فقط
        $query->whereNotNull('email')->where('email', '!=', '');

        return $query->with(['clientType', 'category'])->get();
    }

    /**
     * معاينة الحملة - عدد العملاء والأسماء الأولى
     */
    public function previewCampaign(array $filters): array
    {
        $customers = $this->getTargetedCustomers($filters);

        return [
            'total' => $customers->count(),
            'preview' => $customers->take(10)->map(fn($client) => [
                'id' => $client->id,
                'name' => $client->cname,
                'email' => $client->email,
                'address' => $client->address,
            ])->toArray(),
        ];
    }

    /**
     * إنشاء سجلات الإرسال للعملاء المستهدفين
     */
    public function createCampaignLogs(Campaign $campaign): int
    {
        $customers = $this->getTargetedCustomers($campaign->target_filters ?? []);

        $logs = [];
        foreach ($customers as $customer) {
            if (!$customer->email) {
                continue;
            }

            $logs[] = [
                'campaign_id' => $campaign->id,
                'client_id' => $customer->id,
                'email' => $customer->email,
                'status' => 'pending',
                'tracking_code' => Str::random(32),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($logs)) {
            CampaignLog::insert($logs);
        }

        $campaign->update(['total_recipients' => count($logs)]);

        return count($logs);
    }

    /**
     * استبدال المتغيرات في الرسالة بقيم العميل
     */
    public function replaceVariables(string $message, Client $client): string
    {
        $variables = [
            '{اسم_العميل}' => $client->cname,
            '{الاسم}' => $client->cname,
            '{العنوان}' => $client->address ?? '',
            '{البريد}' => $client->email ?? '',
            '{الهاتف}' => $client->phone ?? '',
            '{الشركة}' => $client->company ?? '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $message);
    }

    /**
     * إضافة روابط التتبع للرسالة
     */
    public function addTrackingLinks(string $message, Campaign $campaign): string
    {
        // البحث عن جميع الروابط في الرسالة
        preg_match_all('/<a\s+href=["\']([^"\']+)["\'][^>]*>/i', $message, $matches);

        if (empty($matches[1])) {
            return $message;
        }

        foreach ($matches[1] as $url) {
            // إنشاء رابط تتبع
            $trackingCode = Str::random(16);
            
            CampaignLink::create([
                'campaign_id' => $campaign->id,
                'original_url' => $url,
                'tracking_code' => $trackingCode,
            ]);

            $trackingUrl = route('campaigns.track.click', $trackingCode);
            $message = str_replace($url, $trackingUrl, $message);
        }

        return $message;
    }

    /**
     * إضافة Pixel التتبع للفتح
     */
    public function addTrackingPixel(string $message, string $trackingCode): string
    {
        $pixelUrl = route('campaigns.track.open', $trackingCode);
        $pixel = '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none;" />';

        return $message . $pixel;
    }

    /**
     * تحديث إحصائيات الحملة
     */
    public function updateCampaignStats(Campaign $campaign): void
    {
        $stats = CampaignLog::where('campaign_id', $campaign->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" OR status = "opened" OR status = "clicked" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "opened" OR status = "clicked" THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN status = "clicked" THEN 1 ELSE 0 END) as clicked,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
            ')
            ->first();

        $campaign->update([
            'total_sent' => $stats->sent ?? 0,
            'total_opened' => $stats->opened ?? 0,
            'total_clicked' => $stats->clicked ?? 0,
            'total_failed' => $stats->failed ?? 0,
        ]);
    }
}

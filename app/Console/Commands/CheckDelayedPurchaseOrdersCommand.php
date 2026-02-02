<?php

namespace App\Console\Commands;

use App\Models\OperHead;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\Notifications\Notifications\OrderNotification;

class CheckDelayedPurchaseOrdersCommand extends Command
{
    protected $signature = 'purchasing:check-delayed-orders';
    protected $description = 'إنشاء تنبيهات للمستخدمين عند وجود أوامر شراء متأخرة (تجاوز تاريخ الاستلام المتوقع)';

    public function handle(): int
    {
        if (! Schema::hasColumn('operhead', 'expected_delivery_date')) {
            $this->warn('عمود expected_delivery_date غير موجود في operhead. تشغيل المايجريشن أولاً.');
            return 0;
        }

        $query = OperHead::withoutGlobalScopes()
            ->where('pro_type', 15)
            ->where('isdeleted', 0)
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', now()->toDateString());

        if (Schema::hasColumn('operhead', 'workflow_state')) {
            $query->where('workflow_state', 3);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('لا توجد طلبات شراء متأخرة.');
            return 0;
        }

        $url = route('reports.purchasing.delayed-orders');
        $message = "يوجد {$count} أمر شراء متأخر. تاريخ الاستلام المتوقع قد مضى ولم تُستلم بعد.";
        $data = [
            'id' => null,
            'title' => 'تنبيه: طلبات شراء متأخرة',
            'message' => $message,
            'icon' => 'fa-exclamation-triangle',
            'created_at' => now()->toDateTimeString(),
            'url' => $url,
        ];

        $users = User::all();
        foreach ($users as $user) {
            try {
                $user->notify(new OrderNotification($data));
            } catch (\Throwable $e) {
                $this->warn("فشل إرسال تنبيه للمستخدم {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("تم إنشاء تنبيه الطلبات المتأخرة ({$count} أمر) لـ {$users->count()} مستخدم.");
        return 0;
    }
}

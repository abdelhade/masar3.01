<?php

declare(strict_types=1);

namespace Modules\Maintenance\Console;

use Illuminate\Console\Command;
use Modules\Maintenance\Models\PeriodicMaintenanceSchedule;
use Modules\Depreciation\Models\DepreciationItem;
use Modules\Fleet\Models\Vehicle;
use Modules\Notifications\Notifications\OrderNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class CheckMaintenanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:check-maintenance-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for upcoming maintenance and insurance renewals and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Checking for maintenance and insurance reminders...');

        // 1. Check Periodic Maintenance Schedules
        $this->checkMaintenanceSchedules();

        // 2. Check Asset Insurance Renewals
        $this->checkAssetInsuranceRenewals();

        // 3. Check Vehicle Insurance Renewals
        $this->checkVehicleInsuranceRenewals();

        $this->info('Finished checking reminders.');
    }

    protected function checkMaintenanceSchedules(): void
    {
        $schedules = PeriodicMaintenanceSchedule::where('is_active', true)
            ->whereNotNull('next_maintenance_date')
            ->get();

        $this->info("Found {$schedules->count()} active maintenance schedules");

        foreach ($schedules as $schedule) {
            $this->line("Checking schedule: {$schedule->item_name} - Next: {$schedule->next_maintenance_date}");

            if ($schedule->isMaintenanceDueSoon()) {
                $this->warn("✅ Sending notification for: {$schedule->item_name}");
                $this->sendNotification(
                    'تنبيه صيانة قريبة',
                    "موعد صيانة قادم للبايد: {$schedule->item_name} ({$schedule->item_number}) في تاريخ: " . $schedule->next_maintenance_date->format('Y-m-d'),
                    'las la-tools',
                    $schedule->branch_id
                );
            } else {
                $this->line("❌ Not due soon: {$schedule->item_name}");
            }
        }
    }

    protected function checkAssetInsuranceRenewals(): void
    {
        $assets = DepreciationItem::where('is_active', true)
            ->whereNotNull('insurance_renewal_date')
            ->get();

        $this->info("Found {$assets->count()} assets with insurance dates");

        foreach ($assets as $asset) {
            $this->line("Checking asset: {$asset->name} - Insurance: {$asset->insurance_renewal_date}");

            if ($asset->isInsuranceRenewalSoon()) {
                $this->warn("✅ Sending notification for asset: {$asset->name}");
                $this->sendNotification(
                    'تنبيه تجديد تأمين',
                    "موعد تجديد تأمين للأصل: {$asset->name} في تاريخ: " . $asset->insurance_renewal_date->format('Y-m-d'),
                    'las la-shield-alt',
                    $asset->branch_id
                );
            } else {
                $this->line("❌ Not due soon: {$asset->name}");
            }
        }
    }

    protected function checkVehicleInsuranceRenewals(): void
    {
        $vehicles = Vehicle::where('is_active', true)
            ->whereNotNull('insurance_renewal_date')
            ->get();

        $this->info("Found {$vehicles->count()} vehicles with insurance dates");

        foreach ($vehicles as $vehicle) {
            $this->line("Checking vehicle: {$vehicle->name} - Insurance: {$vehicle->insurance_renewal_date}");

            if ($vehicle->isInsuranceRenewalSoon()) {
                $this->warn("✅ Sending notification for vehicle: {$vehicle->name}");
                $this->sendNotification(
                    'تنبيه تجديد تأمين مركبة',
                    "موعد تجديد تأمين للمركبة: {$vehicle->name} ({$vehicle->plate_number}) في تاريخ: " . $vehicle->insurance_renewal_date->format('Y-m-d'),
                    'las la-car-crash',
                    $vehicle->branch_id
                );
            } else {
                $this->line("❌ Not due soon: {$vehicle->name}");
            }
        }
    }

    protected function sendNotification(string $title, string $message, string $icon, ?int $branchId = null): void
    {
        // نبعت للمدير العام وأي مستخدم معاه صلاحية في الفرع ده
        $query = User::where('email', 'admin@admin.com');

        if ($branchId) {
            $query->orWhereHas('branches', function ($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            });
        }

        $users = $query->get();

        $this->info("Sending to {$users->count()} users");

        foreach ($users as $user) {
            $this->line("  → Notifying: {$user->email}");
            $user->notify(new OrderNotification([
                'id' => uniqid(),
                'title' => $title,
                'message' => $message,
                'icon' => $icon,
                'created_at' => now()->toDateTimeString(),
            ]));
        }
    }
}

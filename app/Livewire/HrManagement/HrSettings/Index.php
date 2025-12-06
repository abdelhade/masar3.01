<?php

declare(strict_types=1);

namespace App\Livewire\HrManagement\HrSettings;

use App\Models\HRSetting;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('إعدادات الموارد البشرية')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public function render()
    {
        $setting = HRSetting::getCompanyDefault();

        // إذا لم يكن هناك إعداد، أنشئ واحد افتراضي (يجب أن يكون من الـ seeder)
        if (! $setting) {
            $setting = HRSetting::create([
                'company_max_leave_percentage' => 7.00,
            ]);
        }

        return view('livewire.hr-management.hr-settings.index', [
            'setting' => $setting,
        ]);
    }
}

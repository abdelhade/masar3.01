<?php

declare(strict_types=1);

namespace App\Livewire\HrManagement\HrSettings;

use App\Models\HRSetting;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('إعدادات الموارد البشرية')]
class CreateEdit extends Component
{
    public ?HRSetting $setting = null;

    #[Rule('required|numeric|min:0|max:100')]
    public float $company_max_leave_percentage = 7.00;

    public function mount(?int $settingId = null): void
    {
        // دائماً نحمل الإعداد الحالي (من الـ seeder)
        $this->setting = HRSetting::getCompanyDefault();

        if (! $this->setting) {
            // إذا لم يكن هناك إعداد، أنشئ واحد افتراضي (يجب أن يكون من الـ seeder)
            $this->setting = HRSetting::create([
                'company_max_leave_percentage' => 7.00,
            ]);
        }

        $this->loadSettingData();
    }

    public function loadSettingData(): void
    {
        if ($this->setting) {
            $this->company_max_leave_percentage = (float) $this->setting->company_max_leave_percentage;
        }
    }

    public function save(): void
    {
        $this->validate();

        // دائماً تعديل فقط (لا إنشاء جديد)
        if (! $this->setting) {
            $this->setting = HRSetting::getCompanyDefault();
            if (! $this->setting) {
                $this->addError('general', __('hr.hr_setting_not_found'));

                return;
            }
        }

        $this->setting->update([
            'company_max_leave_percentage' => $this->company_max_leave_percentage,
        ]);

        session()->flash('message', __('hr.hr_setting_updated_successfully'));
        $this->redirect(route('hr.settings.index'));
    }

    public function render()
    {
        return view('livewire.hr-management.hr-settings.create-edit');
    }
}

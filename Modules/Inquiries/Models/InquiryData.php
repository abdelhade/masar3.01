<?php

namespace Modules\Inquiries\Models;

use App\Models\City;
use App\Models\Town;
use App\Models\Client;
use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Model;
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus};

class InquiryData extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => InquiryStatus::class,
        'status_for_kon' => StatusForKon::class,
        'kon_title' => KonTitle::class,
        'inquiry_date' => 'date',
        'req_submittal_date' => 'date',
        'project_start_date' => 'date',
        'project_documents' => 'array',
        'submittal_checklist' => 'array',
        'working_conditions' => 'array',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function mainContractor()
    {
        return $this->belongsTo(Client::class, 'main_contractor_id')->where('type', ClientType::MainContractor->value);
    }

    public function consultant()
    {
        return $this->belongsTo(Client::class, 'consultant_id')->where('type', ClientType::Consultant->value);
    }

    public function owner()
    {
        return $this->belongsTo(Client::class, 'owner_id')->where('type', ClientType::Owner->value);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    public function getTotalConditionsScoreAttribute()
    {
        $score = 0;
        if (is_array($this->working_conditions)) {
            foreach ($this->working_conditions as $condition) {
                if ($condition['checked'] ?? false) {
                    $score += (int) ($condition['value'] ?? 0);
                }
            }
        }
        return $score;
    }

    public function getTotalSubmittalScoreAttribute()
    {
        $score = 0;
        if (is_array($this->submittal_checklist)) {
            foreach ($this->submittal_checklist as $item) {
                if ($item['checked'] ?? false) {
                    $score += (int) ($item['value'] ?? 0);
                }
            }
        }
        return $score;
    }

    public function getProjectDifficultyAttribute()
    {
        $score = $this->total_conditions_score;
        if ($score < 6) return 1;
        if ($score <= 10) return 2;
        if ($score <= 15) return 3;
        return 4;
    }

    public static function getSubmittalsOptions()
    {
        return [
            ['name' => 'Pre qualification', 'checked' => false, 'value' => 0],
            ['name' => 'Design', 'checked' => false, 'value' => 1],
            ['name' => 'MOS', 'checked' => false, 'value' => 0],
            ['name' => 'Material Submittal', 'checked' => false, 'value' => 1],
            ['name' => 'Methodology', 'checked' => false, 'value' => 1],
            ['name' => 'Time schedule', 'checked' => false, 'value' => 1],
            ['name' => 'Insurances', 'checked' => false, 'value' => 1],
            ['name' => 'Project team', 'checked' => false, 'value' => 1],
        ];
    }

    public static function getConditionsOptions()
    {
        return [
            ['name' => 'Safety level', 'checked' => false, 'options' => ['Normal' => 1, 'Medium' => 2, 'High' => 3], 'value' => 0],
            ['name' => 'Vendor list', 'checked' => false, 'value' => 1],
            ['name' => 'Consultant approval', 'checked' => false, 'value' => 1],
            ['name' => 'Machines approval', 'checked' => false, 'value' => 0],
            ['name' => 'Labours approval', 'checked' => false, 'value' => 0],
            ['name' => 'Security approvals', 'checked' => false, 'value' => 0],
            ['name' => 'Working Hours', 'checked' => false, 'options' => ['Normal(10hr/6 days)' => 1, 'Half week(8hr, 4day)' => 2, 'Half day(4hr/6days)' => 2, 'Half week-Half day(4hr/4day)' => 3], 'value' => 0],
            ['name' => 'Night shift required', 'checked' => false, 'value' => 1],
            ['name' => 'Tight time schedule', 'checked' => false, 'value' => 1],
            ['name' => 'Remote Location', 'checked' => false, 'value' => 2],
            ['name' => 'Difficult Access Site', 'checked' => false, 'value' => 1],
            ['name' => 'Without advance payment', 'checked' => false, 'value' => 1],
            ['name' => 'Payment conditions', 'checked' => false, 'options' => ['CDC' => 0, 'PDC 30 days' => 1, 'PDC 90 days' => 2], 'value' => 0],
        ];
    }

    public static function getStatusOptions()
    {
        return InquiryStatus::cases();
    }

    public static function getStatusForKonOptions()
    {
        return StatusForKon::cases();
    }

    public static function getKonTitleOptions()
    {
        return KonTitle::cases();
    }
}

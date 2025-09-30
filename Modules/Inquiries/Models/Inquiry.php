<?php

namespace Modules\Inquiries\Models;

use App\Models\{City, Town, Client, Project};
use App\Enums\ClientType;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Modules\Inquiries\Models\ProjectDocument;
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus, QuotationStateEnum};

class Inquiry extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => InquiryStatus::class,
        'status_for_kon' => StatusForKon::class,
        'kon_title' => KonTitle::class,
        'inquiry_date' => 'date',
        'req_submittal_date' => 'date',
        'project_start_date' => 'date',
        'estimation_start_date' => 'date',
        'estimation_finished_date' => 'date',
        'submitting_date' => 'date',
        'project_documents' => 'array',
        'submittal_checklist' => 'array',
        'working_conditions' => 'array',
    ];

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

    public function assignedEngineer()
    {
        return $this->belongsTo(Client::class, 'assigned_engineer_id')->where('type', ClientType::ENGINEER->value);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    public function submittalChecklists()
    {
        return $this->belongsToMany(SubmittalChecklist::class, 'inquiry_submittal_checklist');
    }

    public function workConditions()
    {
        return $this->belongsToMany(WorkCondition::class, 'inquiry_work_condition');
    }

    public function projectDocuments()
    {
        return $this->belongsToMany(ProjectDocument::class, 'inquiry_project_document');
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    public function inquirySource()
    {
        return $this->belongsTo(InquirySource::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function comments()
    {
        return $this->hasMany(InquiryComment::class)->with('user')->latest();
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

    public static function getQuotationStateOptions()
    {
        return QuotationStateEnum::cases();
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
}

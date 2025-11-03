<?php

namespace Modules\Inquiries\Models;

use App\Models\{City, Town, Project};
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus, QuotationStateEnum};

class Inquiry extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => InquiryStatus::class,
        'status_for_kon' => StatusForKon::class,
        'kon_title' => KonTitle::class,
        'quotation_state' => QuotationStateEnum::class,
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

    // علاقات Contacts بناءً على الأدوار
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'inquiry_contact')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function getContactsByRole($roleName)
    {
        return $this->contacts()
            ->whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })
            ->get();
    }

    // Helper methods للوصول السريع
    public function clients()
    {
        return $this->contacts()->whereHas('roles', function ($query) {
            $query->where('name', 'Client');
        });
    }

    public function mainContractors()
    {
        return $this->contacts()->whereHas('roles', function ($query) {
            $query->where('name', 'Main Contractor');
        });
    }

    public function consultants()
    {
        return $this->contacts()->whereHas('roles', function ($query) {
            $query->where('name', 'Consultant');
        });
    }

    public function owners()
    {
        return $this->contacts()->whereHas('roles', function ($query) {
            $query->where('name', 'Owner');
        });
    }

    public function assignedEngineers()
    {
        return $this->contacts()->whereHas('roles', function ($query) {
            $query->where('name', 'Engineer');
        });
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

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    public function workTypes()
    {
        return $this->belongsToMany(
            WorkType::class,
            'inquiry_work_type',
            'inquiry_id',
            'work_type_id'
        )
            ->withPivot(['hierarchy_path', 'description', 'order'])
            ->orderBy('inquiry_work_type.order')
            ->withTimestamps();
    }

    public function inquirySource()
    {
        return $this->belongsTo(InquirySource::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectSize()
    {
        return $this->belongsTo(ProjectSize::class, 'project_size_id');
    }

    public function comments()
    {
        return $this->hasMany(InquiryComment::class)->with('user')->latest();
    }

    public function quotationUnits()
    {
        return $this->belongsToMany(QuotationUnit::class, 'inquiry_quotation_info', 'inquiry_id', 'quotation_unit_id')
            ->withPivot('quotation_type_id')
            ->withTimestamps();
    }

    public function projectDocuments()
    {
        return $this->belongsToMany(
            InquiryDocument::class,
            'inquiry_project_document',
            'inquiry_id',
            'project_document_id'
        )->withTimestamps();
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

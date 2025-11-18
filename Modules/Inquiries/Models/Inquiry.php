<?php

namespace Modules\Inquiries\Models;

use App\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Auth;
use App\Models\{City, Town, Project};
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

    public function scopeMyDrafts($query, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        return $query->where('is_draft', true)->where('created_by', $userId);
    }

    public function getClientAttribute()
    {
        return $this->contacts()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Client');
            })
            ->first();
    }

    /**
     * Get the first main contractor contact
     */
    public function getMainContractorAttribute()
    {
        return $this->contacts()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Main Contractor');
            })
            ->first();
    }

    public function assignedEngineers()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'inquiry_assigned_engineers',
            'inquiry_id',
            'user_id'
        )
            ->withPivot('assigned_at', 'notes')
            ->withTimestamps();
    }

    public function scopeAssignedToUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return $query->whereHas('assignedEngineers', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }


    /**
     * Get the first consultant contact
     */
    public function getConsultantAttribute()
    {
        return $this->contacts()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Consultant');
            })
            ->first();
    }

    /**
     * Get the first owner contact
     */
    public function getOwnerAttribute()
    {
        return $this->contacts()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Owner');
            })
            ->first();
    }

    /**
     * Get the first assigned engineer contact
     */
    public function getAssignedEngineerAttribute()
    {
        return $this->contacts()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Engineer');
            })
            ->first();
    }

    public function scopeDrafts($query)
    {
        return $query->where('is_draft', true);
    }
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


    // إضافة هذه العلاقة في نموذج Inquiry إذا لم تكن موجودة

    /**
     * Get the user who created this inquiry
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to get inquiries created by specific user
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get formatted creation date
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d M Y, h:i A');
    }

    /**
     * Get formatted update date
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('d M Y, h:i A');
    }

    /**
     * Get days until submission
     */
    public function getDaysUntilSubmissionAttribute()
    {
        if (!$this->req_submittal_date) {
            return null;
        }

        return now()->diffInDays($this->req_submittal_date, false);
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

    // public function assignedEngineers()
    // {
    //     return $this->contacts()->whereHas('roles', function ($query) {
    //         $query->where('name', 'Engineer');
    //     });
    // }

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

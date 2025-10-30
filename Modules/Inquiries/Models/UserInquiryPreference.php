<?php
// Modules/Inquiries/Models/UserInquiryPreference.php

namespace Modules\Inquiries\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserInquiryPreference extends Model
{
    protected $fillable = [
        'user_id',
        'visible_columns',
        'filters',
        'sort_column',
        'sort_direction',
        'per_page'
    ];

    protected $casts = [
        'visible_columns' => 'array',
        'filters' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الأعمدة المتاحة للعرض
    public static function getAvailableColumns()
    {
        return [
            'id' => __('#'),
            'project' => __('Project'),
            'client' => __('Client'),
            'main_contractor' => __('Main Contractor'),
            'consultant' => __('Consultant'),
            'owner' => __('Owner'),
            'inquiry_date' => __('Inquiry Date'),
            'req_submittal_date' => __('Required Submittal Date'),
            'project_start_date' => __('Project Start Date'),
            'status' => __('Status'),
            'status_for_kon' => __('Status For KON'),
            'kon_title' => __('KON Title'),
            'quotation_state' => __('Quotation Status'),
            'work_type' => __('Work Type'),
            'inquiry_source' => __('Inquiry Source'),
            'city' => __('City'),
            'town' => __('Town'),
            'total_project_value' => __('Total Value'),
            'client_priority' => __('Client Priority'),
            'kon_priority' => __('KON Priority'),
            'tender_number' => __('Tender Number'),
            'project_difficulty' => __('Difficulty'),
            'assigned_engineer' => __('Assigned Engineer'),
        ];
    }

    // الأعمدة الافتراضية
    public static function getDefaultColumns()
    {
        return ['id', 'project', 'client', 'inquiry_date', 'status', 'quotation_state'];
    }
}

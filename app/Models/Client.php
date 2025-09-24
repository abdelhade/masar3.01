<?php

namespace App\Models;

use App\Enums\ClientType;
use Modules\CRM\Models\Lead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Modules\Inquiries\Models\InquiryData;

class Client extends Model
{
    protected $fillable = [
        'cname',
        'email',
        'phone',
        'phone2',
        'address',
        'address2',
        'date_of_birth',
        'national_id',
        'contact_person',
        'contact_phone',
        'contact_relation',
        'info',
        'job',
        'gender',
        'isdeleted',
        'tenant',
        'branch_id',
        'is_active',
        'type'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    protected $casts = [
        'date_of_birth' => 'date',
        'type' => ClientType::class,
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id');
    }

    public function projectsAsClient()
    {
        return $this->hasMany(InquiryData::class, 'client_id');
    }

    public function projectsAsMainContractor()
    {
        return $this->hasMany(InquiryData::class, 'main_contractor_id');
    }
}

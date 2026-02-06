<?php

namespace App\Models;

use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ClientType;
use Modules\Branches\Models\Branch;
use Modules\Inquiries\Models\Inquiry;
use Modules\CRM\Models\ClientCategory;
use Illuminate\Database\Eloquent\Model;

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
        'commercial_register',
        'tax_certificate',
        'isdeleted',
        'tenant',
        'branch_id',
        'is_active',
        'client_type_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    protected $casts = [
        'date_of_birth' => 'date',
    ];


    public function clientType()
    {
        return $this->belongsTo(ClientType::class, 'client_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id');
    }

    /*
    public function projectsAsClient()
    {
        return $this->hasMany(\Modules\Inquiries\Models\Inquiry::class, 'client_id');
    }
    */

    public function projectsAsMainContractor()
    {
        return $this->hasMany(Inquiry::class, 'main_contractor_id');
    }

    public function category()
    {
        return $this->belongsTo(ClientCategory::class, 'client_category_id');
    }

    public function getNameAttribute()
    {
        return $this->cname;
    }

    public function projects()
    {
        return $this->hasMany(\Modules\Progress\Models\ProjectProgress::class, 'client_id');
    }

    public function invoices()
    {
        return $this->hasMany(OperHead::class, 'acc2')->where('pro_type', 1); // pro_type 1 = فاتورة مبيعات
    }

    public function operations()
    {
        return $this->hasMany(OperHead::class, 'acc2');
    }
    public function tasks()
    {
        return $this->hasMany(\Modules\CRM\Models\Task::class);
    }

    public function activities()
    {
        return $this->hasMany(\Modules\CRM\Models\Activity::class);
    }

    public function tickets()
    {
        return $this->hasMany(\Modules\CRM\Models\Ticket::class);
    }
}

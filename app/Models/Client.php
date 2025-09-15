<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
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
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

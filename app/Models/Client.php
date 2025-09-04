<?php

namespace App\Models;

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
        'branch',
        'is_active',
        'type'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];
}

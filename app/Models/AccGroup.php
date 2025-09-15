<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccGroup extends Model
{
    protected $table = 'acc_groups';

    protected $fillable = [
        'aname',
        'acc_type',
        'parent_id',
        'crtime',
        'mdtime',
        'code',
        'isdeleted',
        'tenant',
        'branch_id',
    ];

    public $timestamps = false; // لأنك تستخدم crtime و mdtime بدل created_at و updated_at
}

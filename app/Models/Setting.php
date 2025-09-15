<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'company_name',
        'company_add',
        'company_email',
        'company_tel',
        'edit_pass',
        'lic',
        'updateline',
        'acc_rent',
        'startdate',
        'enddate',
        'lang',
        'bodycolor',
        'showhr',
        'showclinc',
        'showatt',
        'showpayroll',
        'showrent',
        'showpay',
        'showtsk',
        'def_pos_client',
        'def_pos_store',
        'def_pos_employee',
        'def_pos_fund',
        'isdeleted',
        'tenant',
        'branch_id',
        'show_all_tasks',
        'logo'
    ];

    public $timestamps = false; // لأن الجدول لا يحتوي على created_at أو updated_at

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

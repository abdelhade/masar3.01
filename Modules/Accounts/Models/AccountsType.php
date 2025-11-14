<?php

namespace Modules\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class AccountsType extends Model
{
    protected $table = 'accounts_types';
    
    protected $fillable = [
        'name',
    ];

    public function accHeads()
    {
        return $this->hasMany(AccHead::class, 'acc_type');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccHead extends Model
{
    protected $table = 'acc_head';
    protected $guarded = [];
    public $timestamps = false;

    public function transfersAsAcc1()
    {
        return $this->hasMany(Transfer::class);
    }

    public function transfersAsAcc2()
    {
        return $this->hasMany(Transfer::class, 'acc2');
    }

    public function operheadsAsAcc1()
    {
        return $this->hasMany(OperHead::class, 'acc1');
    }

    public function operheadsAsAcc2()
    {
        return $this->hasMany(OperHead::class, 'acc2');
    }

    public function employees()
    {
        return $this->hasMany(OperHead::class, 'emp_id');
    }

    public function stores()
    {
        return $this->hasMany(OperHead::class, 'store_id');
    }

    public function users()
    {
        return $this->hasMany(OperHead::class, 'user');
    }
    public function children()
    {
        return $this->hasMany(AccHead::class, 'parent_id')->with('children');
    }

}


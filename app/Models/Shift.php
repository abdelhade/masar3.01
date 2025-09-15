<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{

    protected $table = 'shifts';
    protected $guarded = ['id'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

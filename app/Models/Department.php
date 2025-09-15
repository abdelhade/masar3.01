<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{

    protected $table = 'departments';
    protected $guarded = ['id'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

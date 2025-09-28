<?php

namespace Modules\Branches\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'is_active'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

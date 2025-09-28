<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branches\Models\Branch;
// use Modules\Services\Database\Factories\ServiceTypeFactory;

class ServiceType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'service_categories';
    protected $guarded = ['id'];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    // }
    public function branch(): belongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function services(): hasMany
    {
        return $this->hasMany(Service::class, 'service_type_id');
    }


    protected static function newFactory()
    {
        return \Modules\Services\Database\Factories\ServiceTypeFactory::new();
    }
}

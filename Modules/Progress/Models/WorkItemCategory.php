<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class WorkItemCategory extends Model
{
    use SoftDeletes;
    protected $fillable = ['name'];

    public function workItems(): HasMany
    {
        return $this->hasMany(WorkItem::class, 'category_id');
    }
}

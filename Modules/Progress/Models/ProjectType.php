<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectType extends Model
{
    use SoftDeletes;
    protected $fillable = ['name'];

    public function projects()
    {
        return $this->hasMany(ProjectProgress::class, 'project_type_id');
    }
}

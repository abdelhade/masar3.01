<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectType extends Model
{
    protected $fillable = ['name'];

    public function projects()
    {
        return $this->hasMany(ProjectProgress::class, 'project_type_id');
    }
}

<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;

class Subproject extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name',
        'project_id',
        'project_template_id',
        'weight',
        'unit',
        'start_date',
        'end_date',
        'total_quantity',
        'description'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectProgress::class, 'project_id');
    }

    public function template()
    {
        return $this->belongsTo(ProjectTemplate::class, 'project_template_id');
    }
}

<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Project;

class Issue extends Model
{
    use SoftDeletes;
// 
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'priority',
        'status',
        'reporter_id',
        'assigned_to',
        'module',
        'reproduce_steps',
        'deadline'
    ];

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class, 'project_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(IssueComment::class, 'issue_id')->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(IssueAttachment::class, 'issue_id');
    }
}

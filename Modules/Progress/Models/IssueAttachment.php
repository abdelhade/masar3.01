<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class IssueAttachment extends Model
{
    use SoftDeletes;
// 
    protected $fillable = [
        'issue_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size'
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class, 'issue_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Modules\CRM\Models\CrmClient;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Modules\CRM\Enums\{TaskStatusEnum, TaskPriorityEnum};


class Task extends Model  implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'priority' => TaskPriorityEnum::class,
        'status' => TaskStatusEnum::class,
        'due_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(CrmClient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}

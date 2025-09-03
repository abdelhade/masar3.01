<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Modules\CRM\Models\CrmClient;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Modules\CRM\Enums\{TaskStatusEnum, TaskPriorityEnum};
use Spatie\MediaLibrary\MediaCollections\Models\Media;



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

    public function taskType()
    {
        return $this->belongsTo(\Modules\CRM\Models\TaskType::class, 'task_type_id');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200);
    }

    // تحديد مسار حفظ الملفات
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('tasks')
            ->useDisk('public')
            ->useFallbackUrl('/images/placeholder.jpg')
            ->useFallbackPath(public_path('/images/placeholder.jpg'));
    }
}

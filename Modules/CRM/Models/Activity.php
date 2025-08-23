<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Enums\ActivityTypeEnum;
use App\Models\User;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'title',
        'description',
        'type',
        'scheduled_at',
        'activity_date',
        'client_id',
        'assigned_to',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'scheduled_at' => 'datetime',
        'type' => ActivityTypeEnum::class,
    ];

    public function client()
    {
        return $this->belongsTo(CrmClient::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

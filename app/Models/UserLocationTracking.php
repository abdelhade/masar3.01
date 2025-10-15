<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocationTracking extends Model
{
    protected $guarded = ['id'];
    protected $table = 'user_location_tracking';
    
    protected $casts = [
        'tracked_at' => 'datetime',
        'additional_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('tracked_at', [$start, $end]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'login_at',
        'logout_at',
        'session_id',
        'session_duration',
        'location',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'session_duration' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

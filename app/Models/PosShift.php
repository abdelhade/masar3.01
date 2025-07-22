<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosShift extends Model
{
    protected $fillable = [
        'user_id',
        'pos_id',
        'opening_balance',
        'closing_balance',
        'opened_at',
        'closed_at',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

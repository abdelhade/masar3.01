<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Varibal;

class VaribalValue extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function varibal()
    {
        return $this->belongsTo(Varibal::class);
    }
}

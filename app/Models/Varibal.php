<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VaribalValue;

class Varibal extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function varibalValues()
    {
        return $this->hasMany(VaribalValue::class);
    }
}

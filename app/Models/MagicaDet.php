<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagicaDet extends Model
{
    use HasFactory;

    protected $table = 'magica_dets';
    protected $guarded = [];

    public function magical()
    {
        return $this->belongsTo(Magical::class);
    }
} 
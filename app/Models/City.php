<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\State;
use App\Models\Town;
class City extends Model 
{

    protected $table = 'cities';
    protected $guarded = ['id'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function towns()
    {
        return $this->hasMany(Town::class);
    }

}
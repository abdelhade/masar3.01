<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\State;
class Country extends Model 
{

    protected $table = 'countries';
    protected $guarded = ['id'];

    public function states()
    {
        return $this->hasMany(State::class);
    }

}
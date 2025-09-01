<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\City;
class Town extends Model
{
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

}

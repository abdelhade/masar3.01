<?php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
        'description',

    ];
}

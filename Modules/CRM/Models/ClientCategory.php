<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'title',
        'pro_type',
        'op_id',
        'amount',
        'account_id',
        'description',
    ];
}

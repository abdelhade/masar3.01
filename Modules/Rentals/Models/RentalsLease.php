<?php

namespace Modules\Rentals\Models;

use App\Models\Client;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class RentalsLease extends Model
{

    protected $fillable = [
        'unit_id',
        'client_id',
        'start_date',
        'end_date',
        'rent_amount',
        'payment_method',
        'status',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    // }

    public function unit()
    {
        return $this->belongsTo(RentalsUnit::class, 'unit_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function account()
    {
        return $this->belongsTo(AccHead::class, 'acc_id');
    }

    // public function branch()
    // {
    //     return $this->belongsTo(Branch::class, 'branch_id');
    // }
}

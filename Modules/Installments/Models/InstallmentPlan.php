<?php

namespace Modules\Installments\Models;

use Modules\Accounts\Models\AccHead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstallmentPlan extends Model
{
    protected $fillable = [
        'client_id',
        'acc_head_id',
        'invoice_id',
        'total_amount',
        'down_payment',
        'amount_to_be_installed',
        'number_of_installments',
        'start_date',
        'interval_type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
    ];

    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function account()
    {
        return $this->belongsTo(AccHead::class, 'acc_head_id');
    }
}

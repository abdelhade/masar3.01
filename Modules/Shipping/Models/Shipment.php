<?php

declare(strict_types=1);

namespace Modules\Shipping\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'tracking_number',
        'shipping_company_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'zone',
        'weight',
        'length',
        'width',
        'height',
        'package_value',
        'status',
        'estimated_delivery_date',
        'actual_delivery_date',
        'shipping_cost',
        'insurance_cost',
        'additional_fees',
        'total_cost',
        'priority',
        'notes',
        'internal_notes',
        'branch_id',
    ];

    protected $casts = [
        'estimated_delivery_date' => 'datetime',
        'actual_delivery_date' => 'datetime',
        'shipping_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'additional_fees' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'package_value' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function shippingCompany()
    {
        return $this->belongsTo(ShippingCompany::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ratings()
    {
        return $this->hasMany(ShippingCompanyRating::class);
    }

    public function calculateShippingCost($zone = null)
    {
        $baseCost = $this->shippingCompany->base_rate ?? 0;
        $weightCost = 0;

        if ($zone) {
            $shippingZone = ShippingZone::where('code', $zone)->first();
            if ($shippingZone) {
                $baseCost = $shippingZone->base_rate;
                $weightCost = $this->weight * $shippingZone->rate_per_kg;
            }
        }

        $this->shipping_cost = $baseCost + $weightCost;

        // حساب التأمين (1% من قيمة الطرد)
        if ($this->package_value) {
            $this->insurance_cost = $this->package_value * 0.01;
        }

        // رسوم إضافية للشحن السريع
        if ($this->priority === 'express') {
            $this->additional_fees = $this->shipping_cost * 0.5;
        } elseif ($this->priority === 'urgent') {
            $this->additional_fees = $this->shipping_cost * 0.25;
        }

        $this->total_cost = $this->shipping_cost + $this->insurance_cost + $this->additional_fees;

        return $this->total_cost;
    }
}

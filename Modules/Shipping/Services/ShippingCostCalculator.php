<?php

namespace Modules\Shipping\Services;

use Modules\Shipping\Models\ShippingZone;

class ShippingCostCalculator
{
    public function calculate($weight, $zoneCode, $priority = 'normal', $packageValue = 0)
    {
        $zone = ShippingZone::where('code', $zoneCode)->where('is_active', true)->first();
        
        if (!$zone) {
            return [
                'shipping_cost' => 0,
                'insurance_cost' => 0,
                'additional_fees' => 0,
                'total_cost' => 0,
                'estimated_days' => 0,
            ];
        }

        $shippingCost = $zone->base_rate + ($weight * $zone->rate_per_kg);
        $insuranceCost = $packageValue > 0 ? $packageValue * 0.01 : 0;
        $additionalFees = 0;

        if ($priority === 'express') {
            $additionalFees = $shippingCost * 0.5;
        } elseif ($priority === 'urgent') {
            $additionalFees = $shippingCost * 0.25;
        }

        $totalCost = $shippingCost + $insuranceCost + $additionalFees;

        return [
            'shipping_cost' => round($shippingCost, 2),
            'insurance_cost' => round($insuranceCost, 2),
            'additional_fees' => round($additionalFees, 2),
            'total_cost' => round($totalCost, 2),
            'estimated_days' => $zone->estimated_days,
        ];
    }
}

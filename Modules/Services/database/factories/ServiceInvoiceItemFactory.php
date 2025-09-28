<?php

namespace Modules\Services\Database\Factories;

use Modules\Services\Models\ServiceInvoiceItem;
use Modules\Services\Models\ServiceInvoice;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceInvoiceItemFactory extends Factory
{
    protected $model = ServiceInvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(3, 0.1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $discountPercentage = $this->faker->randomFloat(2, 0, 20);
        $taxPercentage = $this->faker->randomFloat(2, 0, 15);

        $lineSubtotal = $quantity * $unitPrice;
        $discountAmount = ($lineSubtotal * $discountPercentage) / 100;
        $discountedAmount = $lineSubtotal - $discountAmount;
        $taxAmount = ($discountedAmount * $taxPercentage) / 100;
        $lineTotal = $discountedAmount + $taxAmount;

        return [
            'service_invoice_id' => ServiceInvoice::factory(),
            'service_id' => Service::factory(),
            'service_unit_id' => ServiceUnit::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'description' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function withService(Service $service): static
    {
        return $this->state(fn (array $attributes) => [
            'service_id' => $service->id,
            'service_unit_id' => $service->service_unit_id,
            'unit_price' => $service->price,
        ]);
    }

    public function withServiceUnit(ServiceUnit $serviceUnit): static
    {
        return $this->state(fn (array $attributes) => [
            'service_unit_id' => $serviceUnit->id,
        ]);
    }

    public function withDiscount(float $percentage): static
    {
        return $this->state(function (array $attributes) use ($percentage) {
            $lineSubtotal = $attributes['quantity'] * $attributes['unit_price'];
            $discountAmount = ($lineSubtotal * $percentage) / 100;
            $discountedAmount = $lineSubtotal - $discountAmount;
            $taxAmount = ($discountedAmount * $attributes['tax_percentage']) / 100;
            $lineTotal = $discountedAmount + $taxAmount;

            return [
                'discount_percentage' => $percentage,
                'discount_amount' => $discountAmount,
                'line_total' => $lineTotal,
            ];
        });
    }

    public function withTax(float $percentage): static
    {
        return $this->state(function (array $attributes) use ($percentage) {
            $lineSubtotal = $attributes['quantity'] * $attributes['unit_price'];
            $discountAmount = ($lineSubtotal * $attributes['discount_percentage']) / 100;
            $discountedAmount = $lineSubtotal - $discountAmount;
            $taxAmount = ($discountedAmount * $percentage) / 100;
            $lineTotal = $discountedAmount + $taxAmount;

            return [
                'tax_percentage' => $percentage,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
            ];
        });
    }

    public function noDiscount(): static
    {
        return $this->state(function (array $attributes) {
            $lineSubtotal = $attributes['quantity'] * $attributes['unit_price'];
            $taxAmount = ($lineSubtotal * $attributes['tax_percentage']) / 100;
            $lineTotal = $lineSubtotal + $taxAmount;

            return [
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'line_total' => $lineTotal,
            ];
        });
    }

    public function noTax(): static
    {
        return $this->state(function (array $attributes) {
            $lineSubtotal = $attributes['quantity'] * $attributes['unit_price'];
            $discountAmount = ($lineSubtotal * $attributes['discount_percentage']) / 100;
            $lineTotal = $lineSubtotal - $discountAmount;

            return [
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'line_total' => $lineTotal,
            ];
        });
    }
}

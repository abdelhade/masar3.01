<?php

namespace Modules\Services\Database\Factories;

use Modules\Services\Models\ServiceInvoice;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceInvoiceFactory extends Factory
{
    protected $model = ServiceInvoice::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['buy', 'sell']);
        $invoiceDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $dueDate = $this->faker->optional(0.7)->dateTimeBetween($invoiceDate, '+30 days');

        return [
            'type' => $type,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'supplier_id' => $type === 'buy' ? $this->faker->numberBetween(1, 5) : null,
            'customer_id' => $type === 'sell' ? $this->faker->numberBetween(1, 5) : null,
            'branch_id' => 1, // Use the default branch ID
            'subtotal' => $this->faker->randomFloat(2, 100, 5000),
            'tax_amount' => $this->faker->randomFloat(2, 0, 500),
            'discount_amount' => $this->faker->randomFloat(2, 0, 200),
            'total_amount' => $this->faker->randomFloat(2, 100, 5500),
            'notes' => $this->faker->optional(0.4)->paragraph(2),
            'terms_conditions' => $this->faker->optional(0.3)->paragraph(1),
            'status' => $this->faker->randomElement(['draft', 'pending', 'approved', 'rejected', 'cancelled']),
            'created_by' => 1, // Use existing user ID
            'updated_by' => $this->faker->optional(0.3)->passthrough(1), // Use existing user ID or null
            'approved_by' => $this->faker->optional(0.2)->passthrough(1), // Use existing user ID or null
            'approved_at' => $this->faker->optional(0.2)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function buy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'buy',
            'supplier_id' => $this->faker->numberBetween(1, 5),
            'customer_id' => null,
        ]);
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sell',
            'supplier_id' => null,
            'customer_id' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => 1, // Use existing user ID
            'approved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => 1, // Use existing user ID
            'approved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function withBranch(int $branchId): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branchId,
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($invoice) {
            // Create 1-3 items for each invoice
            $itemCount = $this->faker->numberBetween(1, 3);
            
            for ($i = 0; $i < $itemCount; $i++) {
                $service = \Modules\Services\Models\Service::inRandomOrder()->first();
                $quantity = $this->faker->numberBetween(1, 5);
                $unitPrice = $service ? $service->price : $this->faker->randomFloat(2, 50, 500);
                $discountPercentage = $this->faker->randomFloat(2, 0, 20);
                $discountAmount = ($unitPrice * $quantity * $discountPercentage) / 100;
                $taxPercentage = $this->faker->randomFloat(2, 0, 15);
                $taxAmount = (($unitPrice * $quantity) - $discountAmount) * ($taxPercentage / 100);
                $lineTotal = ($unitPrice * $quantity) - $discountAmount + $taxAmount;

                \Modules\Services\Models\ServiceInvoiceItem::create([
                    'service_invoice_id' => $invoice->id,
                    'service_id' => $service ? $service->id : null,
                    'service_unit_id' => $service && $service->serviceUnit ? $service->serviceUnit->id : null,
                    'description' => $service ? $service->name : $this->faker->sentence(3),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);
            }
        });
    }
}

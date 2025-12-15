<?php

namespace Modules\Checks\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Checks\Models\Check;

class CheckFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Check::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $bankNames = [
            'البنك الأهلي السعودي',
            'بنك الرياض',
            'البنك السعودي الفرنسي',
            'البنك السعودي للاستثمار',
            'بنك الجزيرة',
            'البنك العربي الوطني',
        ];

        $issueDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $dueDate = $this->faker->dateTimeBetween($issueDate, '+6 months');

        return [
            'check_number' => $this->faker->unique()->numerify('#########'),
            'bank_name' => $this->faker->randomElement($bankNames),
            'account_number' => $this->faker->numerify('####-####-####-####'),
            'account_holder_name' => $this->faker->name(),
            'amount' => $this->faker->randomFloat(2, 100, 100000),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'payment_date' => null,
            'status' => $this->faker->randomElement([
                Check::STATUS_PENDING,
                Check::STATUS_CLEARED,
                Check::STATUS_BOUNCED,
                Check::STATUS_CANCELLED,
            ]),
            'type' => $this->faker->randomElement([
                Check::TYPE_INCOMING,
                Check::TYPE_OUTGOING,
            ]),
            'payee_name' => $this->faker->optional()->name(),
            'payer_name' => $this->faker->optional()->name(),
            'notes' => $this->faker->optional()->sentence(),
            'reference_number' => $this->faker->optional()->numerify('REF-####'),
            'attachments' => null,
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'oper_id' => null,
        ];
    }

    /**
     * Indicate that the check is incoming.
     */
    public function incoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Check::TYPE_INCOMING,
        ]);
    }

    /**
     * Indicate that the check is outgoing.
     */
    public function outgoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Check::TYPE_OUTGOING,
        ]);
    }

    /**
     * Indicate that the check is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Check::STATUS_PENDING,
            'payment_date' => null,
        ]);
    }

    /**
     * Indicate that the check is cleared.
     */
    public function cleared(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Check::STATUS_CLEARED,
            'payment_date' => $this->faker->dateTimeBetween($attributes['due_date'], 'now'),
        ]);
    }

    /**
     * Indicate that the check is bounced.
     */
    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Check::STATUS_BOUNCED,
            'payment_date' => null,
        ]);
    }

    /**
     * Indicate that the check is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Check::STATUS_CANCELLED,
            'payment_date' => null,
        ]);
    }

    /**
     * Indicate that the check is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Check::STATUS_PENDING,
            'due_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }
}

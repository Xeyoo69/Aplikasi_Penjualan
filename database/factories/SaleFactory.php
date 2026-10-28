<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(10_000, 500_000);

        return [
            'invoice_no' => 'INV-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'user_id' => User::factory(),
            'customer_id' => null,
            'subtotal' => $total,
            'discount_type' => null,
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'total' => $total,
            'paid_amount' => $total,
            'change_amount' => 0,
            'payment_method' => PaymentMethod::Cash,
            'status' => SaleStatus::Completed,
        ];
    }
}

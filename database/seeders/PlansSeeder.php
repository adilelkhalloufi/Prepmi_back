<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Boîte de 4 repas',
                'meals_per_week' => 4,
                'price_per_week' => 180, // Set your price
                'points_value' => 2,
                'delivery_fee' => 0.00,
                'is_free_shipping' => false,
                'is_active' => true,
                
            ],
            [
                'name' => 'Boîte de 6 repas',
                'meals_per_week' => 6,
                'price_per_week' => 265, // Set your price
                'points_value' => 3,
                'delivery_fee' => 0.00,
                'is_free_shipping' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Boîte de 8 repas',
                'meals_per_week' => 8,
                'price_per_week' => 345, // Set your price
                'points_value' => 4,
                'delivery_fee' => 0.00,
                'is_free_shipping' => false,
                'is_active' => true,
                'price_subscription_per_week' => 330,
            ],
            [
                'name' => 'Boîte de 10 repas',
                'meals_per_week' => 10,
                'price_per_week' => 415, // Set your price
                'points_value' => 5,
                'delivery_fee' => 0.00,
                'is_free_shipping' => false,
                'is_active' => true,
                'price_subscription_per_week' => 400,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                [
                    'name' => $plan['name'],
                    'meals_per_week' => $plan['meals_per_week'],
                ],
                $plan
            );
        }

        $this->command->info('Plans seeded successfully.');
    }
}

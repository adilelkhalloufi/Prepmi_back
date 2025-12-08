<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Membership',
                'description' => 'Perfect for individuals looking to enjoy regular meal delivery with basic benefits.',
                'monthly_fee' => 29.99,
                'discount_percentage' => 5.00,
                'delivery_slots' => 2,
                'includes_free_desserts' => false,
                'free_desserts_quantity' => 0,
                'perks' => [
                    '5% discount on all orders',
                    '2 weekly delivery slots',
                    'Priority customer support',
                    'Access to member-only meals',
                ],
                'is_active' => true,
                'billing_day_of_month' => 1,
            ],
            [
                'name' => 'Premium Membership',
                'description' => 'Enhanced benefits for frequent customers with free desserts and better discounts.',
                'monthly_fee' => 49.99,
                'discount_percentage' => 10.00,
                'delivery_slots' => 3,
                'includes_free_desserts' => true,
                'free_desserts_quantity' => 2,
                'perks' => [
                    '10% discount on all orders',
                    '3 weekly delivery slots',
                    '2 free desserts per month',
                    'Priority customer support',
                    'Access to member-only meals',
                    'Early access to new menu items',
                    'Free meal customization',
                ],
                'is_active' => true,
                'billing_day_of_month' => 1,
            ],
            [
                'name' => 'VIP Membership',
                'description' => 'Ultimate dining experience with maximum benefits and exclusive perks.',
                'monthly_fee' => 79.99,
                'discount_percentage' => 15.00,
                'delivery_slots' => 5,
                'includes_free_desserts' => true,
                'free_desserts_quantity' => 4,
                'perks' => [
                    '15% discount on all orders',
                    '5 weekly delivery slots',
                    '4 free desserts per month',
                    'VIP customer support',
                    'Access to member-only meals',
                    'Early access to new menu items',
                    'Free meal customization',
                    'Complimentary birthday meal',
                    'Free express delivery',
                    'Monthly chef-special surprise',
                ],
                'is_active' => true,
                'billing_day_of_month' => 1,
            ],
            [
                'name' => 'Family Plan',
                'description' => 'Ideal for families with multiple weekly deliveries and generous benefits.',
                'monthly_fee' => 99.99,
                'discount_percentage' => 12.00,
                'delivery_slots' => 6,
                'includes_free_desserts' => true,
                'free_desserts_quantity' => 6,
                'perks' => [
                    '12% discount on all orders',
                    '6 weekly delivery slots',
                    '6 free desserts per month',
                    'Priority customer support',
                    'Access to member-only meals',
                    'Early access to new menu items',
                    'Free meal customization',
                    'Family portion sizes available',
                    'Kids meal options included',
                ],
                'is_active' => true,
                'billing_day_of_month' => 1,
            ],
            [
                'name' => 'Corporate Membership',
                'description' => 'Designed for businesses providing meal benefits to employees.',
                'monthly_fee' => 199.99,
                'discount_percentage' => 20.00,
                'delivery_slots' => 10,
                'includes_free_desserts' => true,
                'free_desserts_quantity' => 10,
                'perks' => [
                    '20% discount on all orders',
                    '10 weekly delivery slots',
                    '10 free desserts per month',
                    'Dedicated account manager',
                    'Bulk order management',
                    'Customized meal plans',
                    'Invoice billing available',
                    'Employee wellness reports',
                    'Nutritionist consultation',
                    'Flexible delivery scheduling',
                ],
                'is_active' => true,
                'billing_day_of_month' => 1,
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::create($plan);
        }
    }
}

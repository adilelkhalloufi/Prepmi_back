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
                'name' => 'PrepMe Membership',
                'description' => 'PrepMe Membership — 99 MAD / Month\n✔ Free delivery\n✔ –5 MAD per meal\n✔ Members-only premium meals\n✔ Extra delivery day\n✔ 2 free desserts monthly\n✔ Cancel anytime',
                'monthly_fee' => 99.00,
                'discount_percentage' => 0.00,  
                'fixed_discount_amount' => 5.00,
                'delivery_slots' => 3,
                'includes_free_desserts' => true,
                'free_desserts_quantity' => 2,
                'perks' => [
                    'FREE DELIVERY (ALWAYS) - Members pay 0 MAD for delivery on every order',
                    '–5 MAD ON EVERY STANDARD MEAL - Standard meal price: 60 MAD, Member price: 55 MAD (Chicken, Beef, Vegan, Shrimp standard meals)',
                    'ACCESS TO PREMIUM MEALS (MEMBERS-ONLY) - Premium meals: Salmon, Steak bites, Pot roast, Seasonal specials (with +8 to +12 MAD upgrade fee)',
                    '3 DELIVERY DAYS / WEEK - Extra delivery day compared to non-members',
                    '2 FREE DESSERTS / MONTH - Automatically added on the first order of the month',
                    'PAUSE / FREEZE FLEXIBILITY - Pause anytime, 1 free freeze every 6 months, rejoin anytime',
                ],
                'is_active' => true,
                'billing_day_of_month' => 1,
                'free_delivery' => true,
                'has_premium_access' => true,
                'premium_upgrade_fee_min' => 8.00,
                'premium_upgrade_fee_max' => 12.00,
                'free_freezes_per_period' => 1,
                'freeze_period_months' => 6,
                'cancellable_anytime' => true,
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::create($plan);
        }
    }
}

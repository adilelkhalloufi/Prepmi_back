<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'system_points_per_order',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Target points to reach for a free meal reward',
            ],
            [
                'key' => 'system_points_referral',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Points awarded for referrals',
            ],
            [
                'key' => 'app_name',
                'value' => 'Prepmi',
                'type' => 'string',
                'description' => 'Application name',
            ],
            [
                'key' => 'show_prices_to_guests',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Show prices to guest users',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

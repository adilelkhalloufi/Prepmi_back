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
                'key' => 'order_sizes',
                'value' => '["small", "large"]',
                'type' => 'json',
                'description' => 'Available order sizes',
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
            [
                'key' => 'youtube_explanation_video',
                'value' => 'https://www.youtube.com/embed/CRd8dHqU1AM?si=o3QPG9FPq-QEeL4c',
                'type' => 'string',
                'description' => 'YouTube video explaining the app',
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

<?php

namespace Database\Seeders;

use App\enum\SlotType;
use App\Models\DeliverySlot;
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // day_of_week: 0=Sunday, 1=Monday, ..., 6=Saturday, null=every day
    public function run(): void
    {
        $slots = [
            // Morning slots - Available to all users
            [
                'slot_name' => 'Early Morning (All Users)',
                'slot_type' => SlotType::BOTH,
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'max_capacity' => 15,
                'current_bookings' => 0,
                'day_of_week' => 0,
                'is_active' => true,
                'price_adjustment' => 0.00,
                'description' => 'Early morning delivery slot available for all users',
            ],
            [
                'slot_name' => 'Late Morning (All Users)',
                'slot_type' => SlotType::MEMBERSHIP,
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'max_capacity' => 20,
                'current_bookings' => 0,
                'day_of_week' => 3,
                'is_active' => true,
                'price_adjustment' => 0.00,
                'description' => 'Late morning delivery slot available for all users',
            ],
            // Evening slots - Premium slots for membership users
            [
                'slot_name' => 'Evening Premium (Membership Only)',
                'slot_type' => SlotType::BOTH,
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'max_capacity' => 10,
                'current_bookings' => 0,
                'day_of_week' => 5,
                'is_active' => true,
                'price_adjustment' => 5.00,
                'description' => 'Exclusive evening delivery slot for membership users with a premium charge',
            ],
    


        ];

        foreach ($slots as $slot) {
            DeliverySlot::create($slot);
        }
    }
}

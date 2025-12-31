<?php

namespace Database\Seeders;

use App\Models\MealType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MealTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mealTypes = [
            [
                'name' => 'Menu',
                'slug' => 'menu',
                'description' => 'Regular menu meals',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Breakfast',
                'slug' => 'breakfast',
                'description' => 'Breakfast meals',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Drinks',
                'slug' => 'drinks',
                'description' => 'Beverages and drinks',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts',
                'description' => 'Sweet treats and desserts',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($mealTypes as $mealType) {
            MealType::create($mealType);
        }
    }
}

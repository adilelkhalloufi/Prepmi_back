<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Chicken meals',
                'slug' => 'chicken-meals',
                'description' => 'Affordable and versatile chicken-based meals',
                'is_active' => true,
            ],
            [
                'name' => 'Beef/Lamb meals',
                'slug' => 'beef-lamb-meals',
                'description' => 'Premium and hearty beef and lamb meals',
                'is_active' => true,
            ],
            [
                'name' => 'Fish meals',
                'slug' => 'fish-meals',
                'description' => 'Fresh and light fish-based meals',
                'is_active' => true,
            ],
            [
                'name' => 'Vegetarian meals',
                'slug' => 'vegetarian-meals',
                'description' => 'Plant-based and modern vegetarian meals',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Categories seeded successfully.');
    }
}

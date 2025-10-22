<?php

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\User;
use App\Models\WeeklyMenu;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WeeklyMenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for created_by field
        $admin = User::where('email', 'admin@admin.com')->first();

        if (!$admin) {
            $this->command->error('Admin user not found. Please run DatabaseSeeder first.');
            return;
        }

        // Get all meals
        $meals = Meal::where('is_active', true)->get();

        if ($meals->isEmpty()) {
            $this->command->error('No meals found. Please run MealsSeeder first.');
            return;
        }

        // Create weekly menus for the next 4 weeks
        $weeklyMenus = [];
        
        // Current week
        $currentWeekStart = Carbon::now()->startOfWeek();
        $weeklyMenus[] = [
            'week_start_date' => $currentWeekStart->copy(),
            'week_end_date' => $currentWeekStart->copy()->endOfWeek(),
            'title' => 'Menu de la semaine - ' . $currentWeekStart->format('d M Y'),
            'description' => 'Découvrez notre sélection de repas pour cette semaine avec des plats savoureux et équilibrés',
            'is_active' => true,
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $admin->id,
        ];

        // Next 3 weeks
        for ($i = 1; $i <= 3; $i++) {
            $weekStart = Carbon::now()->addWeeks($i)->startOfWeek();
            $weeklyMenus[] = [
                'week_start_date' => $weekStart->copy(),
                'week_end_date' => $weekStart->copy()->endOfWeek(),
                'title' => 'Menu de la semaine - ' . $weekStart->format('d M Y'),
                'description' => 'Découvrez notre sélection de repas pour cette semaine avec des plats savoureux et équilibrés',
                'is_active' => true,
                'is_published' => $i == 1, // Only publish next week
                'published_at' => $i == 1 ? now() : null,
                'created_by' => $admin->id,
            ];
        }

        // Create the weekly menus and attach meals
        foreach ($weeklyMenus as $index => $menuData) {
            $menu = WeeklyMenu::updateOrCreate(
                ['week_start_date' => $menuData['week_start_date']],
                $menuData
            );

            // Attach meals to the menu
            // For variety, select different meals for each week
            $selectedMeals = $meals->shuffle()->take(12); // Select 12 meals per week
            
            $position = 1;
            foreach ($selectedMeals as $meal) {
                // Randomly mark some meals as featured
                $isFeatured = $position <= 3; // First 3 meals are featured
                
                $menu->meals()->syncWithoutDetaching([
                    $meal->id => [
                        'position' => $position,
                        'is_featured' => $isFeatured,
                        'special_price' => $isFeatured ? $meal->price * 0.9 : null, // 10% discount for featured
                        'availability_count' => rand(20, 50), // Random availability
                        'sold_count' => $index == 0 ? rand(0, 15) : 0, // Only current week has sold items
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
                
                $position++;
            }

            $this->command->info("Weekly menu created for week starting {$menuData['week_start_date']->format('Y-m-d')} with {$selectedMeals->count()} meals");
        }

        $this->command->info('Weekly menus seeded successfully: ' . count($weeklyMenus) . ' menus created.');
    }
}

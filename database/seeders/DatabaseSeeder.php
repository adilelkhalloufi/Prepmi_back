<?php

namespace Database\Seeders;

use App\enum\ProfilStatus;
use App\enum\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoriesSeeder::class,
            PlansSeeder::class,
            MealsSeeder::class,
        ]);

        User::factory()->create([
            'first_name' => 'admin',
            'last_name' => 'admin',
            'email' => 'admin@admin.com',
            'role' => UserRole::ADMIN,
            'status' => ProfilStatus::ACTIF,
            'password' => bcrypt('password'),
        ]);

        $client =   User::factory()->create([
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@test.com',
            'role' => UserRole::CLIENT,
            'status' => ProfilStatus::ACTIF,
            'password' => bcrypt('test'),
        ]);

        $client->rewards()->create([
            'type' => 'free_meal',
            'value' => 45.00,
            'title' => 'Repas PrepMe Gratuit',
            'description' => 'Réduction de 45 MAD applicable sur votre prochaine commande',
            'earned_at' => now(),
            'is_used' => false,
        ]);

        // create rewars for this last user


        // Seed weekly menus after user is created
        $this->call([
            WeeklyMenusSeeder::class,
        ]);
    }
}

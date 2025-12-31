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
            MealTypeSeeder::class,
            MealsSeeder::class,
            MembershipPlanSeeder::class,
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
            'first_name' => 'client',
            'last_name' => 'client',
            'email' => 'client@client.com',
            'role' => UserRole::CLIENT,
            'status' => ProfilStatus::ACTIF,
            'password' => bcrypt('client'),
        ]);

              $client =   User::factory()->create([
            'first_name' => 'cuisinier',
            'last_name' => 'cuisinier',
            'email' => 'cuisinier@cuisinier.com',
            'role' => UserRole::CUISINIER,
            'status' => ProfilStatus::ACTIF,
            'password' => bcrypt('cuisinier'),
        ]);

        $client->rewards()->create([
            'type' => 'free_meal',
            'value' => 45.00,
            'title' => 'Repas PrepMe Gratuit',
            'description' => 'Réduction de 45 MAD applicable sur votre prochaine commande',
            'earned_at' => now(),
            'is_used' => false,
        ]);

    

    
    }
}

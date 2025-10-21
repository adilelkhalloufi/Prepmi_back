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
    }
}

<?php

namespace Database\Seeders;

use App\enum\ProfilStatus;
use App\enum\UserRole;
use App\Models\Specialitie;
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
        // User::factory(10)->create();
        // $this->call(FamilySeeder::class);
        // $this->call(UniteSeeder::class);
        // $this->call(CategoriesSeeder::class);
        // $this->call(TypeSeeder::class);

        // User::factory()->create([
        //     'first_name' => 'Admin Admin',
        //     'last_name' => 'Admin',
        //     'email' => 'admin@admin.com',
        //     'role' => UserRole::ADMIN,
        //     'status' => ProfilStatus::ACTIF,
        //     'specialitie_id' => Specialitie::pluck('id')->random(),
        //     'password' => bcrypt('password'),
        // ]);
        // User::factory()->create([
        //     'first_name' => 'test test',
        //     'last_name' => 'test',
        //     'email' => 'test@test.com',
        //     'role' => UserRole::VENDER,
        //     'status' => ProfilStatus::ACTIF,
        //     'specialitie_id' => Specialitie::pluck('id')->random(),
        //     'password' => bcrypt('password'),
        // ]);
        // User::factory()->create([
        //     'first_name' => 'test2 test2',
        //     'last_name' => 'test2',
        //     'email' => 'test2@test2.com',
        //     'role' => UserRole::SELLER,
        //     'status' => ProfilStatus::ACTIF,
        //     'specialitie_id' => Specialitie::pluck('id')->random(),
        //     'password' => bcrypt('password'),
        // ]);

        // // User::factory(10)->create();

        // $this->call(ProductSeeder::class);
    }
}

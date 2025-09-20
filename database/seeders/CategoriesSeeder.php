<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/Imported/Categories.csv');
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        foreach ($records as $record) {

            DB::table(Categorie::TABLE_NAME)->insert([
                Categorie::COL_NAME => $record[Categorie::COL_NAME],
                Categorie::COL_DESCRIPTION => $record[Categorie::COL_DESCRIPTION],
                Categorie::COL_FAMILY_ID => $record[Categorie::COL_FAMILY_ID],
            ]);
        }
        // show message in console it done for laravel
        $this->command->info('Categories seeded successfully.');
    }
}

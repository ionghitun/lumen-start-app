<?php

use App\Models\Language;
use Illuminate\Database\Seeder;

/**
 * Class LanguagesTableSeeder
 */
class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Language::create([
            'name' => 'English',
            'code' => 'en'
        ]);

        Language::create([
            'name' => 'Română',
            'code' => 'ro'
        ]);
    }
}

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
            'id' => Language::ID_EN,
            'name' => 'English',
            'code' => Language::CODE_EN
        ]);

        Language::create([
            'id' => Language::ID_RO,
            'name' => 'Română',
            'code' => Language::CODE_RO
        ]);
    }
}

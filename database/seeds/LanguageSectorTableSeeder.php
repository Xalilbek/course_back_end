<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSectorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\Msk\LanguageSector::truncate();
//        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $lg = new \App\Models\Msk\LanguageSector();
        $lg->name = "az_sector";
        $lg->save();

        $lg = new \App\Models\Msk\LanguageSector();
        $lg->name = "ru_sector";
        $lg->save();

        $lg = new \App\Models\Msk\LanguageSector();
        $lg->name = "en_sector";
        $lg->save();
    }
}

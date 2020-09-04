<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LanguageSectorTableSeeder::class);
        $this->call(MskSeeder::class);
        $this->call(AuthSeeder::class);
    }
}

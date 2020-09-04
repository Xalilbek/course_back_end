<?php

use App\Models\Msk\City;
use App\Models\Msk\EducationLevel;
use App\Models\Msk\Region;
use App\Models\Msk\Relation;
use App\Models\Msk\School;
use App\Models\Msk\Subject;
use App\Models\Msk\University;
use Illuminate\Database\Seeder;

class MskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = ['Riyaziyyat', 'Fizika', 'Azərbaycan dili', 'İngilis dili', 'Kimya', 'Tarix', 'Coğrafiya'];
        foreach($names as $name){
            $data = new Subject;
            $data->name = $name;
            $data->save();
        }
        $names = ['Ana','Ata','Baba','Nənə','Dayı'];
        foreach($names as $name){
            $data = new Relation;
            $data->name = $name;
            $data->save();
        }
        $names = ['Bakalavr','Magistr','Professor','Alim'];
        foreach($names as $name){
            $data = new EducationLevel;
            $data->name = $name;
            $data->save();
        }
        $names = ['Bakı','Gəncə','Mingəçevir','Subqayıt'];
        foreach($names as $name){
            $data = new City;
            $data->name = $name;
            $data->save();
        }
        $names = ['Səbail','Nizami','Sabunçu','Nardaran'];
        foreach($names as $name){
            $data = new Region;
            $data->name = $name;
            $data->city_id = 1;
            $data->save();
        }
        $names = ['53','236','239','175','52'];
        foreach($names as $name){
            $data = new School;
            $data->name = $name;
            $data->region_id = 1;
            $data->save();
        }
        $names = ['BDU','ADNSU','ADA','ATU'];
        foreach($names as $name){
            $data = new University;
            $data->name = $name;
            $data->save();
        }
    }
}

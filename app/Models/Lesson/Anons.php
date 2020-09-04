<?php

namespace App\Models\Lesson;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Anons extends Model
{
    const ALL = 1;
    const PARENTS = 2;
    const STUDENTS = 3;
    
    public function lesson_groups()
    {
        return $this->belongsToMany(LessonGroup::class,'anons_groups');
    }

    public static function getType($type)
    {
        switch ($type) {
            case 'parents':
                $res = Anons::PARENTS;
                break;
            case 'students':
                $res = Anons::STUDENTS;
                break;
            default:
                $res = Anons::ALL;
                break;
        }
        return $res;
    }
}

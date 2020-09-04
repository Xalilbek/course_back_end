<?php

namespace App\Helpers\Lesson;

use App\Models\Lesson\LessonGroupStudent;

class LessonGroupStudentHelper{

    public static function checkStudentHasLesson($id, $lessons)
    {
        $lessons_id = $lessons->pluck('id');
        $res = LessonGroupStudent::whereHas('lessons',function($q) use($lessons_id){
                    $q->whereIn('lessons.id',$lessons_id);
                })
                ->where('student_id',$id)
                ->exists();
        return $res;
    }
}
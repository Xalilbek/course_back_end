<?php

namespace App\Helpers\Lesson;

use App\Models\Lesson\Lesson;
use Carbon\Carbon;

class LessonGroupHelper{

    public static function teacherHasLesson($lesson)
    {
        $teacher = auth()->user();
        return static::checkHasLesson($teacher, $lesson);
    }

    public static function teacherHasLessonByAdmin($teacher,$lesson)
    {
        return static::checkHasLesson($teacher, $lesson);
    }

    public static function checkHasLesson($teacher,$lesson)
    {
        $teacher_user = $teacher->teacher;
        $lesson_time_sub = Carbon::parse($lesson['time'])->subHours($teacher_user->lesson_hour)->subMinutes($teacher_user->lesson_minute);
        $lesson_time_add = Carbon::parse($lesson['time'])->addHours($teacher_user->lesson_hour)->addMinutes($teacher_user->lesson_minute);
        return Lesson::join('lesson_groups as lg','lg.id','lessons.lesson_group_id')
            ->whereNull('lg.deleted_at')
            ->where('lg.teacher_id',$teacher->id)
            ->where('week_day',$lesson['week_day'])
            ->where('time','>',$lesson_time_sub)
            ->where('time','<',$lesson_time_add)
            ->exists();
    }
}
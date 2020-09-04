<?php

namespace App\Helpers\Lesson;

use App\Models\Lesson\Lesson;
use Carbon\Carbon;

class LessonHelper{

    public static function teacherHasLesson($week_day, $time)
    {
        $teacher = auth()->user()->teacher;
        $lesson_time_sub = Carbon::parse($time)->subHours($teacher->lesson_hour)->subMinutes($teacher->lesson_minute);
        $lesson_time_add = Carbon::parse($time)->addHours($teacher->lesson_hour)->addMinutes($teacher->lesson_minute);
        return Lesson::join('lesson_groups as lg','lg.id','lessons.lesson_group_id')
            ->whereNull('lg.deleted_at')
            ->where('lg.teacher_id',auth()->id())
            ->where('week_day',$week_day)
            ->where('time','>',$lesson_time_sub)
            ->where('time','<',$lesson_time_add)
            ->exists();
    }
}
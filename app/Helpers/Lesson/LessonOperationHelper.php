<?php

namespace App\Helpers\Lesson;

use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonOperation;
use Carbon\Carbon;

class LessonOperationHelper{
    public static function checkHas($lesson_id, $date)
    {
        $check = false;
        $check = LessonOperation::where('date',$date->format('Y-m-d'))
            ->where('lesson_id',$lesson_id)->exists();
        return $check;
    }

    public static function teacherHasLesson($date)
    {
        $teacher = auth()->user()->teacher;
        $week_day = Carbon::parse($date)->dayOfWeek ?: 7;
        $lesson_time_sub = Carbon::parse($date->format('H:i'))->subHours($teacher->lesson_hour)->subMinutes($teacher->lesson_minute);
        $lesson_time_add = Carbon::parse($date->format('H:i'))->addHours($teacher->lesson_hour)->addMinutes($teacher->lesson_minute);
        return Lesson::join('lesson_groups as lg','lg.id','lessons.lesson_group_id')
            ->whereNull('lg.deleted_at')
            ->where('lg.teacher_id',auth()->id())
            ->where('week_day',$week_day)
            ->where('time','>',$lesson_time_sub)
            ->where('time','<',$lesson_time_add)
            ->exists();
    }
}
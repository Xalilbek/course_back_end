<?php

namespace App\Helpers\Lesson;

class LessonDayHelper{

    public static function typesCount($types)
    {
        $count = ['absent'=>0,'in_time'=>0,'late'=>0,'left_earlier'=>0];
        foreach($types as $type)
        {
            $count[$type]++;
        }
        return $count;
    }
}
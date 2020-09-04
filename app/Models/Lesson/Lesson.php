<?php

namespace App\Models\Lesson;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $guarded = [];
    public function lesson_group()
    {
        return $this->belongsTo(LessonGroup::class);
    }

    public function lesson_group_students()
    {
        return $this->belongsToMany(LessonGroupStudent::class,'lesson_students');
    }
    
    public function operation()
    {
        return $this->hasOne(LessonOperation::class);
    }

}

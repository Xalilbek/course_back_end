<?php

namespace App\Models\Lesson;

use App\User;
use Illuminate\Database\Eloquent\Model;

class LessonGroupStudent extends Model
{
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_students');
    }
    public function lesson_group()
    {
        return $this->belongsTo(LessonGroup::class);
    }
}

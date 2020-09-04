<?php

namespace App\Models\Lesson;

use App\User;
use Illuminate\Database\Eloquent\Model;

class LessonDay extends Model
{
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class,'student_id');
    }
}

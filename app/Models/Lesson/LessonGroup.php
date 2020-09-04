<?php

namespace App\Models\Lesson;

use App\Models\Msk\Subject;
use App\Traits\OperationLogTrait;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonGroup extends Model
{
    use SoftDeletes, OperationLogTrait;

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
    public function lesson_group_students()
    {
        return $this->hasMany(LessonGroupStudent::class,'lesson_group_id');
    }
}

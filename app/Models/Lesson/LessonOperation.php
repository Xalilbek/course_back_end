<?php

namespace App\Models\Lesson;

use Illuminate\Database\Eloquent\Model;

class LessonOperation extends Model
{
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}

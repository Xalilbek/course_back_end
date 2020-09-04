<?php

namespace App;

use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonGroup;
use App\Models\Lesson\LessonGroupStudent;
use App\Models\Msk\EducationLevel;
use App\Models\Msk\LanguageSector;
use App\Models\Msk\School;
use App\Models\Msk\Subject;
use App\Models\Msk\University;
use App\Models\PermissionGroup;
use App\Models\Users\TeacherUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAvatarAttribute($value)
    {
        if($value){
            return config('app.url').'/images/avatars/'.$value;
        }
        return null;
    }

    public function getAvatarNameAttribute($value)
    {
        if($this->avatar){
            return str_replace(config('app.url').'/images/avatars/', '', $this->avatar).$value;
        }
        return null;
    }

    public function scopeOnlyTeachers($q)
    {
        $q->where('user_type', 'teacher')->whereNotNull('mobile_phone_verified_at');
    }

    public function scopeActiveTeachers($q)
    {
        $q->onlyTeachers()->whereHas('teacher',function($q){
            $q->where('active',1);
        });
    }

    public function scopeOnlyStudents($q)
    {
        $q->where('user_type', 'student')->whereNotNull('mobile_phone_verified_at');
    }

    public function teacher()
    {
        return $this->hasOne(TeacherUser::class);
    }
    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_students', 'student_id');
    }
    public function lesson_group_students()
    {
        return $this->hasMany(LessonGroupStudent::class, 'student_id');
    }
    public function parents()
    {
        return $this->belongsToMany(User::class,'parent_users','user_id','parent_id')->withPivot('relation_id');
    }
    public function lesson_groups()
    {
        return $this->hasMany(LessonGroup::class, 'teacher_id');
    }
    public function education_levels()
    {
        return $this->belongsToMany(EducationLevel::class, 'education_level_users');
    }
    public function universities()
    {
        return $this->belongsToMany(University::class, 'university_users');
    }
    public function language_sectors()
    {
        return $this->belongsToMany(LanguageSector::class, 'language_sector_users');
    }
    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_users');
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teachers');
    }
    public function permission_groups()
    {
        return $this->belongsToMany(PermissionGroup::class,'permission_group_users');
    }
}

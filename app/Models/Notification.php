<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const INFO = 0;
    const REQUEST_GROUP = 1;
    const ANONS = 2;
    const STUDENT_GROUP_OPERATION = 3;
    const LESSON_OPERATION = 4;
    const GREETING = 5;
    const ATTENDANCE = 6;
    const REQUEST_ABSENT = 7;

    public function getTypeAttribute($value)
    {
        switch ($value) {
            case static::REQUEST_GROUP:
                return 'request_group';
                break;
            case static::ANONS:
                return 'anons';
                break;
            case static::STUDENT_GROUP_OPERATION:
                return 'student_group_operation';
                break;
            case static::LESSON_OPERATION:
                return 'lesson_operation';
                break;
            case static::GREETING:
                return 'greeting';
                break;
            case static::ATTENDANCE:
                return 'attendance';
                break;
            case static::REQUEST_ABSENT:
                return 'request_absent';
                break;
            
            default:
                return 'info';
                break;
        }
    }

    public function scopeUser($q)
    {
        return $q->where('user_id', auth()->id());
    }

    public static function requestGroup($user, $group, $note, $lesson_group_student_id)
    {
        $notification = new static;
        $notification->title = __('message.qrupa_qosulma_isteyi');
        $notification->note = $note;
        $notification->content = __('message.sizin_qrupa_qosulmaq_isteyir',
                                ['fullname'=>$user->fullname,
                                 'group'=>$group->name]);
        $notification->user_id = $group->teacher_id;
        $notification->sender_id = $user->id;
        $notification->type = self::REQUEST_GROUP;
        $notification->seen = 0;
        $notification->related_id = $lesson_group_student_id;
        $notification->save();
    }

    public static function sendAnons($ids, $anons)
    {
        foreach($ids as $id){
            $notification = new static;
            $notification->sender_id = auth()->id();
            $notification->user_id = $id;
            $notification->title = __('message.anons');
            $notification->content = $anons->text;
            $notification->type = self::ANONS;
            $notification->related_id = $anons->id;
            $notification->seen = 0;
            $notification->save();
        }
    }

    public static function sendAcceptGroup($student_id,$content = null,$lesson_group_student_id = null)
    {
        $data = [
            'content'=>$content,
            'title'=>__('message.qrupa_qosulma')
        ];
        static::sendStudentGroupOperation($student_id,$lesson_group_student_id,$data);
    }

    public static function sendDeclineGroup($student_id,$content = null,$lesson_group_student_id = null)
    {
        $data = [
            'content'=>$content,
            'title'=>__('message.qrupa_qosulmanin_legvi')
        ];
        static::sendStudentGroupOperation($student_id,$lesson_group_student_id,$data);
    }

    public static function sendTransferGroup($student_id,$content = null,$lesson_group_student_id = null)
    {
        $data = [
            'content'=>$content,
            'title'=>__('message.qrup_transferi')
        ];
        static::sendStudentGroupOperation($student_id,$lesson_group_student_id,$data);
    }

    public static function addNewGroup($student_id,$content = null,$lesson_group_student_id = null)
    {
        $data = [
            'content'=>$content,
            'title'=>__('message.yeni_qrup')
        ];
        static::sendStudentGroupOperation($student_id,$lesson_group_student_id,$data);
    }

    public static function sendStudentGroupOperation($student_id,$lesson_group_student_id = null,$data)
    {
        $parents_id = User::join('parent_users as pu','pu.parent_id','users.id')
                            ->where('pu.user_id',$student_id)->pluck('pu.parent_id')->toArray();
        $ids = array_merge($parents_id,[$student_id]);
        foreach($ids as $id){
            $notification = new static;
            $notification->type = self::STUDENT_GROUP_OPERATION;
            $notification->sender_id = auth()->id();
            $notification->user_id = $id;
            $notification->title = $data['title'] ?: __('message.basliq_yoxdur');
            $notification->content = $data['content'] ?: __('message.movzu_yoxdur');
            $notification->related_id = $lesson_group_student_id;
            $notification->seen = 0;
            $notification->save();
        }
    }

    
    public static function cancelLesson($students_id,$content = null,$operation_id)
    {
        $title = __('message.dersin_legv_olunmasi');
        static::sendLessonOperation($students_id,$content,$title, $operation_id);
    }

    public static function transferLesson($students_id,$content = null,$operation_id)
    {
        $title = 'Ders vaxtinin deyisdirilmesi';
        static::sendLessonOperation($students_id,$content,$title,$operation_id);
    }
    
    public static function addLesson($students_id,$content = null,$operation_id)
    {
        $title = 'Elave ders kecileceyi haqda bildiris';
        static::sendLessonOperation($students_id,$content,$title,$operation_id);
    }

    public static function sendLessonOperation($students_id,$content = null, $title,$operation_id)
    {
        $parents_id = User::join('parent_users as pu','pu.parent_id','users.id')
        ->whereIn('pu.user_id',$students_id)->pluck('pu.parent_id')->toArray();
        $ids = array_merge($parents_id,$students_id);
        foreach($ids as $id){
            $notification = new static;
            $notification->type = self::LESSON_OPERATION;
            $notification->related_id = $operation_id;
            $notification->sender_id = auth()->id();
            $notification->user_id = $id;
            $notification->title = $title;
            $notification->content = $content ?: __('message.movzu_yoxdur');
            $notification->seen = 0;
            $notification->save();
        }
    }

    public static function sendGreeting($user_id)
    {
        $sender = auth()->user();
        $notification = new static;
        $notification->type = self::GREETING;
        $notification->sender_id = $sender->id;
        $notification->user_id = $user_id;
        $notification->title = 'Tebrik mesaji';
        $notification->content = $sender->fullname. ' sizi tebrik edir';
        $notification->seen = 0;
        $notification->save();
    }

    public static function sendAttendance($lesson_day)
    {
        $parents_id = User::join('parent_users as pu','pu.parent_id','users.id')
                            ->where('pu.user_id',$lesson_day->student->id)->pluck('pu.parent_id')->toArray();
        foreach($parents_id as $parent_id){
            $notification = new static;
            $notification->type = self::ATTENDANCE;
            $notification->related_id = $lesson_day->id;
            $notification->sender_id = auth()->id();
            $notification->user_id = $parent_id;
            $notification->title = 'Davemiyyet bildirisi';
            $notification->content = $lesson_day->student->fullname. ' haqqinda bildiris';
            $notification->seen = 0;
            $notification->save();
        }
    }

    public static function sendRequestAbsent($lesson_day, $note = null)
    {
        $lesson_day->load('student.parents','lesson.lesson_group');
        $parents_id = $lesson_day->student->parents->pluck('id')->toArray();
        $teacher_id = $lesson_day->lesson->lesson_group->teacher_id;
        $ids = array_merge($parents_id,[$teacher_id]);
        foreach($ids as $id){
            $notification = new static;
            $notification->type = self::REQUEST_ABSENT;
            $notification->sender_id = auth()->id();
            $notification->user_id = $id;
            $notification->title = 'Derse gele bilmemek barede bildiris';
            $notification->content = $lesson_day->student->fullname .
                ' '.$lesson_day->date .' tarixinde ' .
                ' ' . $lesson_day->lesson->lesson_group->name . 
                ' qrupundaki derse gele bilmeyeceyini bildirir';
            $notification->related_id = $lesson_day->id;
            $notification->note = $note;
            $notification->seen = 0;
            $notification->save();
        }
    }

}

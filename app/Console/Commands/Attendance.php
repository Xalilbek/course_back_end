<?php

namespace App\Console\Commands;

use App\Models\Lesson\LessonDay;
use App\Models\Notification;
use App\User;
use Illuminate\Console\Command;

class Attendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification attendance';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $lesson_days = LessonDay::with('lesson.lesson_group')->where('date',today()->subDays(1))->get();
        foreach($lesson_days as $lesson_day){
            $parents_id = User::join('parent_users as pu','pu.parent_id','users.id')
            ->where('pu.user_id',$lesson_day->student->id)->pluck('pu.parent_id')->toArray();
            foreach($parents_id as $parent_id){
                $notification = new Notification;
                $notification->type = Notification::ATTENDANCE;
                $notification->related_id = $lesson_day->id;
                $notification->sender_id = $lesson_day->lesson->lesson_group->teacher_id;
                $notification->user_id = $parent_id;
                $notification->title = 'Davemiyyet bildirisi';
                $notification->content = $lesson_day->student->fullname. ' haqqinda bildiris';
                $notification->seen = 0;
                $notification->save();
            } 
        }
    }
}

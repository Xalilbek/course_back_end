<?php

namespace App\Http\Controllers\Lesson;

use App\Helpers\Lesson\LessonOperationHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonOperation;
use App\Models\Notification;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LessonOperationController extends Controller
{
    /**
     * @api {post} /lesson/operation/transfer Ders vaxtinin basqa vaxta kecirilmesi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Date} date_cancel format d-m-Y
     * @apiParam {Date} date_add format d-m-Y H:i
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function transferLesson()
    {
        $validator = validator(request()->all(),[
            'date_cancel' => 'required|date_format:d-m-Y',
            'date_add' => 'required|date_format:d-m-Y H:i',
            'lesson' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::with(['lesson_group'])->findOrFail(request('lesson'));
        $this->authorize('check_id',$lesson->lesson_group->teacher_id);
        $date_cancel = Carbon::parse(request('date_cancel'));
        $date_add = Carbon::parse(request('date_add'));
        $week_day = $date_cancel->dayOfWeek ?: 7;
        if($week_day != $lesson->week_day){
            return $this->sendError('Qeyd etdiyiniz tarixde dersiniz yox idi');
        }
        if($date_add < today() || $date_cancel < today()){
            return $this->sendError('Bu tarix kecmisde qaldi');
        }
        $check_date_cancel = LessonOperationHelper::checkHas(request('lesson'), $date_cancel);
        $check_date_add = LessonOperationHelper::checkHas(request('lesson'), $date_add);
        if($check_date_cancel || $check_date_add){
            return $this->sendError('Artiq movcuddur');
        }
        $check_date_add_teacher = LessonOperationHelper::teacherHasLesson($date_add);
        if($check_date_add_teacher){
            return $this->sendError(__('message.basqa_dersle_ust_uste_dusur'));
        }
        DB::beginTransaction();
        try{
            $operation = new LessonOperation;
            $operation->date = $date_cancel->format('Y-m-d');
            $operation->lesson_id = request('lesson');
            $operation->type = 'cancel';
            $operation->reason = request('reason');
            $operation->save();

            $operation = new LessonOperation;
            $operation->date = $date_add->format('Y-m-d');
            $operation->time = $date_add->format('H:i');
            $operation->lesson_id = request('lesson');
            $operation->type = 'add';
            $operation->reason = request('reason');
            $operation->save();

            $students_id = User::join('lesson_group_students as lgs', 'lgs.student_id', 'users.id')
                ->where('lgs.status','accept')
                ->where('lgs.lesson_group_id',$lesson->lesson_group_id)
                ->pluck('student_id')->toArray();
            Notification::transferLesson($students_id, request('reason'),$operation->id);
            DB::commit();
            return $this->sendSuccess();
        }catch(\Exception $ex){
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * @api {post} /lesson/operation/cancel Dersin gonderilen tarixde legv olunmasi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Date} date format d-m-Y
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function cancelLesson()
    {
        $validator = validator(request()->all(),[
            'date' => 'required|date_format:d-m-Y',
            'lesson' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::with(['lesson_group'])->findOrFail(request('lesson'));
        $this->authorize('check_id',$lesson->lesson_group->teacher_id);
        $date = Carbon::parse(request('date'));
        $week_day = $date->dayOfWeek ?: 7;
        if($week_day != $lesson->week_day){
            return $this->sendError('Daxil etdiyiniz gunde dersiniz yoxdur');
        }
        if($date < today()){
            return $this->sendError('bu tarix artiq kecib');
        }
        if(LessonOperationHelper::checkHas(request('lesson'), $date)){
            return $this->sendError('Artiq movcuddur');
        }
        $operation = new LessonOperation;
        $operation->date = $date->format('Y-m-d');
        $operation->lesson_id = request('lesson');
        $operation->type = 'cancel';
        $operation->reason = request('reason');
        $operation->save();

        $students_id = User::join('lesson_group_students as lgs', 'lgs.student_id', 'users.id')
                ->where('lgs.status','accept')
                ->where('lgs.lesson_group_id',$lesson->lesson_group_id)
                ->pluck('student_id')->toArray();
        Notification::cancelLesson($students_id, request('reason'), $operation->id);

        return $this->sendSuccess();
    }

    /**
     * @api {post} /lesson/operation/cancel Gonderilen tarixde elave ders kecilmesi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Date} date format d-m-Y H:i
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function addLesson()
    {
        $validator = validator(request()->all(),[
            'date' => 'required|date_format:d-m-Y H:i',
            'lesson' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::with(['lesson_group'])->findOrFail(request('lesson'));
        $this->authorize('check_id',$lesson->lesson_group->teacher_id);
        $date = Carbon::parse(request('date'));
        $week_day = $date->dayOfWeek ?: 7;
        if($week_day != $lesson->week_day){
            return $this->sendError();
        }
        if($date < today()){
            return $this->sendError('bu tarix artiq kecib');
        }
        if(LessonOperationHelper::checkHas(request('lesson'), $date)){
            return $this->sendError('Artiq movcuddur');
        }
        $check_date_add_teacher = LessonOperationHelper::teacherHasLesson($date);
        if($check_date_add_teacher){
            return $this->sendError(__('message.basqa_dersle_ust_uste_dusur'));
        }
        $operation = new LessonOperation;
        $operation->date = $date->format('Y-m-d');
        $operation->time = $date->format('H:i');
        $operation->lesson_id = request('lesson');
        $operation->type = 'add';
        $operation->reason = request('reason');
        $operation->save();

        $students_id = User::join('lesson_group_students as lgs', 'lgs.student_id', 'users.id')
                ->where('lgs.status','accept')
                ->where('lgs.lesson_group_id',$lesson->lesson_group_id)
                ->pluck('student_id')->toArray();
        Notification::addLesson($students_id, request('reason'), $operation->id);
        return $this->sendSuccess();
    }
    /**
     * @api {delete} /lesson/operation/:id Delete lesson operation
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson operation id
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function deleteLessonOperation($id)
    {
        $operation = LessonOperation::findOrFail($id);
        $this->authorize('check_id',$operation->lesson->lesson_group->teacher_id);
        $operation->delete();
        return $this->sendSuccess();
    }
}

<?php

namespace App\Http\Controllers\Lesson;

use App\Helpers\Lesson\LessonGroupStudentHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonGroup;
use App\Models\Lesson\LessonGroupStudent;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class LessonGroupStudentController extends Controller
{

    /**
     * @api {post} /lesson/student Qrupa qosulma isteyi gondermek
     * @apiGroup Student
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Array} lessons unique lesson id
     * @apiParam {String} [note]
     * 
     *  @apiParamExample {json} Request-Example:
     * {
     *      "lessons":[1,2],
     *       "note":"test note"
     *  }
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":{
     *  "inserted_id":3
     * }
     * }
     */
    public function addLessonGroupStudent()
    {
        $validator = validator(request()->all(), [
            'lessons' => 'required|array',
            'lessons.*' => 'required|integer',
            'note' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $lessons = Lesson::findOrFail(request('lessons'));
            $checkHasLesson = LessonGroupStudentHelper::checkStudentHasLesson(auth()->id(), $lessons);
            if($checkHasLesson){
                return $this->sendError(__('message.derse_2ci_defe_yazila_bilmersiz'));
            }
            $first_lesson = $lessons[0];
            foreach($lessons as $lesson){
                if($lesson->lesson_group_id != $first_lesson->lesson_group_id){
                    return $this->sendError();
                }
            }
            $group = $first_lesson->lesson_group;
            $user = auth()->user();
            $lesson_group_student = LessonGroupStudent::where('student_id',$user->id)
                ->where('lesson_group_id',$group->id)->first();
            if(!$lesson_group_student){
                $lesson_group_student = new LessonGroupStudent;
                $lesson_group_student->lesson_group_id = $group->id;
                $lesson_group_student->student_id = $user->id;
                $lesson_group_student->status = 'none';
                $lesson_group_student->save();
            }
            $lesson_group_student->lessons()->attach(request('lessons'));
            Notification::requestGroup($user, $group, request('note'), $lesson_group_student->id);
            
            DB::commit();
            return $this->sendSuccess(['inserted_id'=>$lesson_group_student->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * @api {post} /lesson/teacher/accept Muellimin sagirdi qrupa qebul etmesi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique student id
     * @apiParam {Integer} group unique lesson group id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function studentAccept()
    {
        $validator = validator(request()->all(), [
            'student' => 'required|integer',
            'group' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson_group_student = LessonGroupStudent::with('lesson_group')->where('student_id',request('student'))
                                                    ->where('lesson_group_id',request('group'))
                                                    ->firstOrFail();
        $this->authorize('check_id', $lesson_group_student->lesson_group->teacher_id);
        $lesson_group_student->status = 'accept';
        $lesson_group_student->reason = request('reason');
        $lesson_group_student->save();
        Notification::sendAcceptGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
        return $this->sendSuccess();
    }

    /**
     * @api {post} /lesson/teacher/decline Muellimin sagirdi qrupa qebul etmemesi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique student id
     * @apiParam {Integer} group unique lesson group id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function studentDecline()
    {
        $validator = validator(request()->all(), [
            'student' => 'required|integer',
            'group' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson_group_student = LessonGroupStudent::with('lesson_group')->where('student_id',request('student'))
                                                    ->where('lesson_group_id',request('group'))
                                                    ->firstOrFail();
        $this->authorize('check_id', $lesson_group_student->lesson_group->teacher_id);
        $lesson_group_student->status = 'decline';
        $lesson_group_student->reason = request('reason');
        $lesson_group_student->save();
        Notification::sendDeclineGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
        return $this->sendSuccess();
    }

    /**
     * @api {post} /lesson/teacher/transfer Muellimin sagirdi basqa qrupa transfer etmesi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique student id
     * @apiParam {Integer} old_group unique lesson group id
     * @apiParam {Integer} new_group unique lesson group id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function studentTransfer()
    {
        $validator = validator(request()->all(), [
            'student' => 'required|integer',
            'old_group' => 'required|integer',
            'new_group' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $lesson_group_student = LessonGroupStudent::with(['lesson_group','lessons'])
            ->where('student_id',request('student'))
            ->where('lesson_group_id',request('old_group'))
            ->firstOrFail();
            $this->authorize('check_id', $lesson_group_student->lesson_group->teacher_id);
            $new_group = LessonGroup::with('lessons')->findOrFail(request('new_group'));
            $this->authorize('check_id', $new_group->teacher_id);
            $lesson_group_student->lesson_group_id = $new_group->id;
            $lesson_group_student->reason = request('reason');
            $lesson_group_student->save();
            $new_lessons_id = $new_group->lessons->pluck('id');
            $lesson_group_student->lessons()->sync($new_lessons_id);
            Notification::sendTransferGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
            DB::commit();
            return $this->sendSuccess();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * @api {post} /lesson/teacher/add Muellimin sagirdi basqa qrupa da elave etmesi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique student id
     * @apiParam {Integer} group unique lesson group id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":{
     *  "inserted_id":3
     * }
     * }
     */
    public function studentAddNewGroup()
    {
        $validator = validator(request()->all(), [
            'student' => 'required|integer',
            'group' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $lesson_group = LessonGroup::with('lessons')->findOrFail(request('group'));
            $this->authorize('check_id', $lesson_group->teacher_id);
            $lesson_group_student = LessonGroupStudent::with(['lesson_group','lessons'])
                ->where('student_id',request('student'))
                ->where('lesson_group_id',request('group'))
                ->first();
            if(!$lesson_group_student){
                $lesson_group_student = new LessonGroupStudent;
                $lesson_group_student->student_id = request('student');
            }
            $lesson_group_student->lesson_group_id = $lesson_group->id;
            $lesson_group_student->status = 'accept';
            $lesson_group_student->reason = request('reason');
            $lesson_group_student->save();
            $lessons_id = $lesson_group->lessons->pluck('id');
            $lesson_group_student->lessons()->sync($lessons_id);
            Notification::addNewGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
            DB::commit();
            return $this->sendSuccess(['inserted_id'=>$lesson_group_student->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }
    
    /**
     * @api {post} /lesson/change_status change student status
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} lesson_group_student unique lesson_group_student id
     * @apiParam {Enum} status (in accept,decline,none)
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function changeStudentStatus()
    {
        $validator = validator(request()->all(), [
            'lesson_group_student' => 'required|integer',
            'status' => 'required|in:accept,decline,none',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson_group_student = LessonGroupStudent::findOrFail(request('lesson_group_student'));
        $this->authorize('check_id', $lesson_group_student->lesson_group->teacher_id);

        $lesson_group_student->status = request('status');
        $lesson_group_student->reason = request('reason');
        $lesson_group_student->save();
        return $this->sendSuccess();
    }

    /**
     * @api {post} /lesson/teacher/lesson/accept Muellimin sagirdi qrupa qebul etmesi (lesson_id-ye gore)
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Integer} student unique student id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function studentAcceptByLessonId()
    {
        $validator = validator(request()->all(), [
            'lesson' => 'required|integer',
            'student'=> 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson_group_student = LessonGroupStudent::with('lesson_group')
                                ->whereHas('lessons',function($q){
                                    $q->where('lesson_students.lesson_id',request('lesson'));
                                })
                                ->where('student_id',request('student'))
                                ->firstOrFail();
        $this->authorize('check_id', $lesson_group_student->lesson_group->teacher_id);
        $lesson_group_student->status = 'accept';
        $lesson_group_student->reason = request('reason');
        $lesson_group_student->save();
        Notification::sendAcceptGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
        return $this->sendSuccess();
    }

    /**
     * @api {post} /lesson/teacher/decline Muellimin sagirdi qrupa qebul etmemesi (lesson_id-ye gore)
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Integer} student unique student id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function studentDeclineByLessonId()
    {
        $validator = validator(request()->all(), [
            'lesson' => 'required|integer',
            'student'=> 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson_group_student = LessonGroupStudent::with('lesson_group')
                                ->whereHas('lessons',function($q){
                                    $q->where('lesson_students.lesson_id',request('lesson'));
                                })
                                ->where('student_id',request('student'))
                                ->firstOrFail();
        $this->authorize('check_id', $lesson_group_student->lesson_group->teacher_id);
        $lesson_group_student->status = 'decline';
        $lesson_group_student->reason = request('reason');
        $lesson_group_student->save();
        Notification::sendDeclineGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
        return $this->sendSuccess();
    }

    /**
     * @api {post} /lesson/teacher/lesson/transfer Muellimin sagirdi basqa qrupa transfer etmesi (lesson_id-ye gore)
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Integer} student unique student id
     * @apiParam {Integer} new_group unique lesson group id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function studentTransferByLessonId()
    {
        $validator = validator(request()->all(), [
            'lesson' => 'required|integer',
            'student' => 'required|integer',
            'new_group' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $lesson_group_student = LessonGroupStudent::with(['lesson_group','lessons'])
                ->whereHas('lessons',function($q){
                    $q->where('lesson_students.lesson_id',request('lesson'));
                })
                ->where('student_id',request('student'))
                ->firstOrFail();
            $this->authorize('check_id', $lesson_group_student->lesson_group->teacher_id);
            $new_group = LessonGroup::with('lessons')->findOrFail(request('new_group'));
            $this->authorize('check_id', $new_group->teacher_id);
            $lesson_group_student->lesson_group_id = $new_group->id;
            $lesson_group_student->reason = request('reason');
            $lesson_group_student->save();
            $new_lessons_id = $new_group->lessons->pluck('id');
            $lesson_group_student->lessons()->sync($new_lessons_id);
            Notification::sendTransferGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
            DB::commit();
            return $this->sendSuccess();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }

        /**
     * @api {post} /lesson/teacher/lesson/add Muellimin sagirdi basqa qrupa da elave etmesi (lesson_id-ye gore)
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique student id
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":{
     *  "inserted_id":3
     * }
     * }
     */
    public function studentAddNewGroupByLessonId()
    {
        $validator = validator(request()->all(), [
            'student' => 'required|integer',
            'lesson' => 'required|integer',
            'reason' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $lesson_group = LessonGroup::with('lessons')
                ->whereHas('lessons', function($q){
                    $q->where('lessons.id',request('lesson'));
                })
                ->firstOrFail();
            $this->authorize('check_id', $lesson_group->teacher_id);
            $lesson_group_student = LessonGroupStudent::with(['lesson_group','lessons'])
                ->where('student_id',request('student'))
                ->whereHas('lessons',function($q){
                    $q->where('lesson_students.lesson_id',request('lesson'));
                })
                ->first();
            if(!$lesson_group_student){
                $lesson_group_student = new LessonGroupStudent;
                $lesson_group_student->student_id = request('student');
            }
            $lesson_group_student->lesson_group_id = $lesson_group->id;
            $lesson_group_student->status = 'accept';
            $lesson_group_student->reason = request('reason');
            $lesson_group_student->save();
            $lessons_id = $lesson_group->lessons->pluck('id');
            $lesson_group_student->lessons()->sync($lessons_id);
            Notification::addNewGroup($lesson_group_student->student_id,request('reason'),$lesson_group_student->id);
            DB::commit();
            return $this->sendSuccess(['inserted_id'=>$lesson_group_student->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }
}

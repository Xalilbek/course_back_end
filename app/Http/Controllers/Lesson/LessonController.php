<?php

namespace App\Http\Controllers\Lesson;

use App\Helpers\Lesson\LessonHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonGroup;
use Carbon\Carbon;

class LessonController extends Controller
{
    /**
     * @api {post} /lesson/lesson Create lesson
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} lesson_group unique lesson group id
     * @apiParam {Integer} week_day
     * @apiParam {String} time format(H:i)
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function createLesson()
    {
        $validator = validator(request()->all(),[
            'lesson_group' => 'required|integer',
            'week_day' => 'required|integer|between:1,7',
            'time' => 'required|date_format:H:i'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson_group = LessonGroup::findOrFail(request('lesson_group'));
        $this->authorize('check_id',$lesson_group->teacher_id);
        if(LessonHelper::teacherHasLesson(request('week_day'), request('time'))){
            return $this->sendError(__('message.basqa_dersle_ust_uste_dusur'));
        }
        $lesson = new Lesson;
        $lesson->lesson_group_id = $lesson_group->id;
        $lesson->week_day = request('week_day');
        $lesson->time = request('time');
        $lesson->save();
        return $this->sendSuccess();
    }

    /**
     * @api {put} /lesson/lesson/:id Update lesson
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson id
     * @apiParam {Integer} week_day
     * @apiParam {String} time format(H:i)
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function updateLesson($id)
    {
        $validator = validator(request()->all(),[
            'week_day' => 'required|integer|between:1,7',
            'time' => 'required|date_format:H:i'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::findOrFail($id);
        $this->authorize('check_id',$lesson->lesson_group->teacher_id);
        if(LessonHelper::teacherHasLesson(request('week_day'), request('time'))){
            return $this->sendError(__('message.basqa_dersle_ust_uste_dusur'));
        }
        $lesson->week_day = request('week_day');
        $lesson->time = request('time');
        $lesson->save();
        return $this->sendSuccess();
    }

    /**
     * @api {delete} /lesson/lesson/:id Delete lesson
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson id
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function deleteLesson($id)
    {
        $lesson = Lesson::findOrFail($id);
        $this->authorize('check_id',$lesson->lesson_group->teacher_id);
        $lesson->delete();
        return $this->sendSuccess();
    }
    
    /**
     * @api {get} /lesson/teacher/lessons_day_by_date Muellimin gonderilen tarixde olan dersleri
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
     * @apiSuccessExample Success-Response:
     *{
     *    "current_page": 1,
     *    "data": [
     *        {
     *            "id": 1,
     *            "lesson_group_id": 1,
     *            "week_day": 1,
     *            "time": "10:00:00",
     *            "lesson_group": {
     *                "id": 1,
     *                "name": "Qrup 1",
     *                "subject_id": 1,
     *                "subject": {
     *                    "id": 1,
     *                    "name": "Riyaziyyat"
     *                }
     *            },
     *            "operation": {
     *                "id": 2,
     *                "lesson_id": 1,
     *                "date": "2020-05-07",
     *                "time": "12:00:00",
     *                "type": "add",
     *                "reason": null
     *            }
     *        },
     *        {
     *            "id": 5,
     *            "lesson_group_id": 3,
     *            "week_day": 4,
     *            "time": "12:00:00",
     *            "lesson_group": {
     *                "id": 3,
     *                "name": "Qrup 2",
     *                "subject_id": 2,
     *                "subject": {
     *                    "id": 2,
     *                    "name": "Fizika"
     *                }
     *            },
     *            "operation": null
     *        }
     *    ],
     *    "first_page_url": "http://localhost:8000/lesson/teacher/lessons_day_by_date?page=1",
     *    "from": 1,
     *    "last_page": 1,
     *    "last_page_url": "http://localhost:8000/lesson/teacher/lessons_day_by_date?page=1",
     *    "next_page_url": null,
     *    "path": "http://localhost:8000/lesson/teacher/lessons_day_by_date",
     *    "per_page": 10,
     *    "prev_page_url": null,
     *    "to": 2,
     *    "total": 2
     *}
     */
    public function getLessonsDayByDate()
    {
        $validator = validator(request()->all(), [
            'date' => 'required|date_format:d-m-Y'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $carbon_date = Carbon::parse(request('date'));
        $date = $carbon_date->format('Y-m-d');
        $day = $carbon_date->dayOfWeek ?: 7;

        $lessons = Lesson::with(['lesson_group' => function ($q) {
                    $q->with(['subject:id,name'])->select(['id','name','subject_id']);
                },
                'operation' => function ($q) use ($date) {
                    $q->where('date',$date)->select(['id','lesson_id','date','time','type','reason']);
                }])
        ->whereHas('lesson_group', function ($q) {
            $q->where('teacher_id', auth()->id());
        })
        ->where('week_day', $day)
        ->orWhereHas('operation', function($q) use($date){
            $q->where('date',$date);
        });

        return $lessons->paginate(10,['id','lesson_group_id','week_day','time']);
    }
}

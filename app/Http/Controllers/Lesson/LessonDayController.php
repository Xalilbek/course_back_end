<?php

namespace App\Http\Controllers\Lesson;

use App\Helpers\Lesson\LessonDayHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonDay;
use App\Models\Lesson\LessonGroup;
use App\Models\Notification;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LessonDayController extends Controller
{
    /**
     * @api {get} lesson/lesson_day/lesson/:id get lesson days by lesson id
     * @apiGroup Student
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson id
     * @apiSuccessExample Success-Response:
     *{
     *    "current_page": 1,
     *    "data": [
     *        {
     *            "id": 3,
     *            "student_id": 29,
     *            "lesson_id": 1,
     *            "date": "2020-04-02",
     *            "mark_home": 50,
     *            "note_home": "eeee",
     *            "mark_lesson": 60,
     *            "note_lesson": "aaa",
     *            "type": "in_time",
     *            "reason": null,
     *            "created_at": "2020-04-15 13:39:36",
     *            "updated_at": "2020-04-15 13:41:36",
     *            "student": {
     *                "id": 29,
     *                "fullname": null,
     *                "avatar": null
     *            }
     *        },
     *        {
     *            "id": 4,
     *            "student_id": 29,
     *            "lesson_id": 1,
     *            "date": "2020-04-03",
     *            "mark_home": null,
     *            "note_home": null,
     *            "mark_lesson": 60,
     *            "note_lesson": "aaa",
     *            "type": "in_time",
     *            "reason": null,
     *            "created_at": "2020-04-15 13:44:30",
     *            "updated_at": "2020-04-15 13:44:30",
     *            "student": {
     *                "id": 29,
     *                "fullname": null,
     *                "avatar": null
     *            }
     *        }
     *    ],
     *    "first_page_url": "api.egundelik.com/lesson/lesson_day/1?page=1",
     *    "from": 1,
     *    "last_page": 1,
     *    "last_page_url": "api.egundelik.com/lesson/lesson_day/1?page=1",
     *    "next_page_url": null,
     *    "path": "api.egundelik.com/lesson/lesson_day/1",
     *    "per_page": 10,
     *    "prev_page_url": null,
     *    "to": 2,
     *    "total": 2
     *}
     */
    public function getLessonDayByLessonId($id)
    {
        $lesson_day = LessonDay::with(['student:id,fullname,avatar'])
        ->whereHas('lesson',function($q) use ($id){
            $q->where('id',$id);
        })
        ->whereHas('lesson.lesson_group',function($q){
            $q->where('teacher_id',auth()->id());
        });
        return $lesson_day->paginate(10);
    }

    /**
     * @api {get} lesson/lesson_day/parent_seen/log get parent seen log
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
     * @apiParam {Date} [start_date]
     * @apiParam {Date} [end_date]
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "date": "2020-04-20",
     *                "parent_seen": 0
     *            }
     *        ],
     *        "first_page_url": "api.egundelik.com/lesson/lesson_day/parent_seen_log?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "api.egundelik.com/lesson/lesson_day/parent_seen_log?page=1",
     *        "next_page_url": null,
     *        "path": "api.egundelik.com/lesson/lesson_day/parent_seen_log",
     *        "per_page": 10,
     *        "prev_page_url": null,
     *        "to": 1,
     *        "total": 1
     *    }
     *}
     */
    public function parentSeenLog()
    {
        return $this->getLogs(['date','parent_seen']);
    }

    /**
     * @api {get} lesson/lesson_day/home_work/log get home work log
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
     * @apiParam {Date} [start_date]
     * @apiParam {Date} [end_date]
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "mark_home": 10,
     *                "note_home": null,
     *                "date": "2020-04-20"
     *            }
     *        ],
     *        "first_page_url": "api.egundelik.com/lesson/lesson_day/home_work/log?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "api.egundelik.com/lesson/lesson_day/home_work/log?page=1",
     *        "next_page_url": null,
     *        "path": "api.egundelik.com/lesson/lesson_day/home_work/log",
     *        "per_page": 10,
     *        "prev_page_url": null,
     *        "to": 1,
     *        "total": 1
     *    }
     *}
     */
    public function homeWorkLog()
    {
        return $this->getLogs(['mark_home','note_home','date']);
    }

    /**
     * @api {get} lesson/lesson_day/lesson_work/log get lesson work log
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
     * @apiParam {Date} [start_date]
     * @apiParam {Date} [end_date]
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "mark_lesson": 10,
     *                "note_lesson": null,
     *                "date": "2020-04-20"
     *            }
     *        ],
     *        "first_page_url": "api.egundelik.com/lesson/lesson_day/lesson_work/log?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "api.egundelik.com/lesson/lesson_day/lesson_work/log?page=1",
     *        "next_page_url": null,
     *        "path": "api.egundelik.com/lesson/lesson_day/lesson_work/log",
     *        "per_page": 10,
     *        "prev_page_url": null,
     *        "to": 1,
     *        "total": 1
     *    }
     *}
     */
    public function lessonWorkLog()
    {
        return $this->getLogs(['mark_lesson','note_lesson','date']);
    }

    /**
     * @api {get} lesson/lesson_day/attendance/log get attendance log
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
     * @apiParam {Date} [start_date]
     * @apiParam {Date} [end_date]
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "type": "in_time",
     *                "date": "2020-04-20"
     *            }
     *        ],
     *        "first_page_url": "api.egundelik.com/lesson/lesson_day/attendance/log?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "api.egundelik.com/lesson/lesson_day/attendance/log?page=1",
     *        "next_page_url": null,
     *        "path": "api.egundelik.com/lesson/lesson_day/attendance/log",
     *        "per_page": 10,
     *        "prev_page_url": null,
     *        "to": 1,
     *        "total": 1
     *    }
     *}
     */
    public function attendanceLog()
    {
        return $this->getLogs(['type','date']);
    }

    /**
     * @api {get} lesson/lesson_day/excellent_student excellent student this week
     * @apiGroup Student
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} lesson unique lesson id
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "student_id": 31,
     *        "fullname": "Sagirdov sagird",
     *        "mark_home": "50.0000",
     *        "mark_lesson": "60.0000",
     *        "avg_total": "55.00000000"
     *    }
     *}
     */
    public function excellentStudent()
    {
        $validator = validator(request()->all(),[
            'lesson' => 'required|integer'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson = Lesson::with('lesson_group.lessons')->findOrFail(request('lesson'));
        $this->authorize('check_id',$lesson->lesson_group->teacher_id);
        $lessons_id = $lesson->lesson_group->lessons->pluck('id')->toArray();
        $excellent_student = LessonDay::join('users as u', 'u.id','lesson_days.student_id')
            ->whereIn('lesson_id',$lessons_id)
            ->whereBetween('date',[now()->subDays(8),now()->subDays(1)])
            ->select(
                'u.id as student_id','u.fullname',
                DB::raw("AVG(mark_home) as mark_home,
                    AVG(mark_lesson) as mark_lesson,
                    AVG((mark_home+mark_lesson)/2) as avg_total"))
            ->groupBy('u.fullname','u.id')
            ->orderByDesc('avg_total')
            ->first();
        return $this->sendSuccess($excellent_student);
    }

    
    /**
     * @api {post} lesson/lesson_day/excellent_student/greeting Elacini tebrik et
     * @apiGroup Student
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique user id
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": null
     *}
     */
    public function greetingExcellentStudent()
    {
        $validator = validator(request()->all(),[
            'student' => 'required|integer|exists:users,id'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        Notification::sendGreeting(request('student'));
        return $this->sendSuccess();
    }
    
    /**
     * @api {post} lesson/lesson_day/student/absent Sagird dersde istirak ede bilmeyeceyini bildirir
     * @apiGroup Student
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Date} [date] default = now
     * @apiParam {String} [note]
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function studentAbsent(){
        $validator = validator(request()->all(),[
            'lesson' => 'required|integer|exists:lessons,id',
            'date' => 'nullable|date_format:Y-m-d',
            'note'=>'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        $date = request('date') ? Carbon::parse(request('date')) : now();
        $lesson_day = LessonDay::where('student_id',auth()->id())
        ->where('lesson_id',request('lesson'))
        ->where('date',$date->format('Y-m-d'))->first();
        if(!$lesson_day){
            $lesson_day = new LessonDay;
            $lesson_day->student_id = auth()->id();
            $lesson_day->lesson_id = request('lesson');
            $lesson_day->date = $date->format('Y-m-d');
        }
        $lesson_day->type = 'absent';
        $lesson_day->save();
        Notification::sendRequestAbsent($lesson_day, request('note'));
        return $this->sendSuccess();
    }

    /**
     * @api {post} lesson/lesson_day/type ad or update type in lesson day
     * @apiGroup Teacher
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique user id
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Date} [date] default = now
     * @apiParam {Enum} type (in:absent,in_time,late,left_earlier)
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": {
     *          "inserted_id": 7
     *      }
     *  }
     */
    public function addOrUpdateType()
    {
        $validator = validator(request()->all(),[
            'student'=>'required|integer|exists:users,id',
            'lesson'=>'required|integer',
            'date' => 'nullable|date_format:Y-m-d',
            'type'=>'required|in:absent,in_time,late,left_earlier',
            'reason'=>'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::with('lesson_group')->findOrFail(request('lesson'));
        $this->authorize('check_id', $lesson->lesson_group->teacher_id);
        $date = request('date') ?: now()->format('Y-m-d');
        $lesson_day = LessonDay::where('student_id',request('student'))
            ->where('lesson_id',request('lesson'))
            ->where('date',$date)->first();
        if(!$lesson_day){
            $lesson_day = new LessonDay;
            $lesson_day->student_id = request('student');
            $lesson_day->lesson_id = request('lesson');
            $lesson_day->date = $date;
        }
        $lesson_day->type = request('type');
        $lesson_day->reason = request('reason');
        $lesson_day->save();
        return $this->sendSuccess(['inserted_id'=>$lesson_day->id]);
    }

    /**
     * @api {get} lesson/lesson_day/student show attendance student by lesson id
     * @apiGroup Student
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique user id
     * @apiParam {Integer} lesson unique lesson id
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "id": 3,
     *                "student_id": 31,
     *                "lesson_id": 1,
     *                "date": "2020-04-13",
     *                "mark_home": 50,
     *                "note_home": "eeee",
     *                "mark_lesson": 60,
     *                "note_lesson": "aaa",
     *                "type": "in_time",
     *                "reason": null,
     *                "created_at": "2020-04-15 13:39:36",
     *                "updated_at": "2020-04-15 13:41:36"
     *            },
     *            {
     *                "id": 7,
     *                "student_id": 31,
     *                "lesson_id": 1,
     *                "date": "2020-04-17",
     *                "mark_home": null,
     *                "note_home": null,
     *                "mark_lesson": null,
     *                "note_lesson": null,
     *                "type": "absent",
     *                "reason": null,
     *                "created_at": "2020-04-17 13:31:25",
     *                "updated_at": "2020-04-17 13:31:25"
     *            }
     *        ],
     *        "first_page_url": "api.egundelik.com/lesson/lesson_day/attendance/1?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "api.egundelik.com/lesson/lesson_day/attendance/1?page=1",
     *        "next_page_url": null,
     *        "path": "api.egundelik.com/lesson/lesson_day/attendance/1",
     *        "per_page": 10,
     *        "prev_page_url": null,
     *        "to": 2,
     *        "total": 2
     *    }
     *}
     */
    public function showAttendanceStudent()
    {
        $validator = validator(request()->all(),[
            'student'=>'required|integer|exists:users,id',
            'lesson'=>'required|integer'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson_day = LessonDay::where('lesson_id',request('lesson'))
            ->where('student_id',request('student'))
            ->paginate(10);
        return $this->sendSuccess($lesson_day);
    }

    /**
     * @api {get} lesson/lesson_day/show/:id show attendance student
     * @apiGroup Student
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson day id
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "id": 4,
     *        "student_id": 30,
     *        "lesson_id": 1,
     *        "date": "2020-04-18",
     *        "mark_home": null,
     *        "note_home": null,
     *        "mark_lesson": null,
     *        "note_lesson": null,
     *        "type": "absent",
     *        "reason": null,
     *        "parent_seen": 1,
     *        "created_at": "2020-04-18 13:41:51",
     *        "updated_at": "2020-04-18 13:46:01",
     *        "student": {
     *            "id": 30,
     *            "fullname": "Quliyev Ceyhun"
     *        }
     *    }
     *}
     */
    public function showLessonDay($id)
    {
        $lesson_day = LessonDay::with('student:id,fullname')->findOrFail($id);
        $lesson_day->parent_seen = 1;
        $lesson_day->save();
        return $this->sendSuccess($lesson_day);
    }

    /**
     * @api {get} lesson/lesson_day/seen_attendance ValideynLerin davamiyyeti izleme sayi
     * @apiGroup Teacher
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} group unique group id
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "student_id": 31,
     *                "student_fullname": "Sagirdov sagird",
     *                "parent_id": 32,
     *                "parent_fullname": "Sagirdin Valideyni",
     *                "seen": 0,
     *                "not_seen": 1
     *            }
     *        ],
     *        "first_page_url": "api.egundelik.com/lesson/lesson_day/seen_attendance?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "api.egundelik.com/lesson/lesson_day/seen_attendance?page=1",
     *        "next_page_url": null,
     *        "path": "api.egundelik.com/lesson/lesson_day/seen_attendance",
     *        "per_page": 10,
     *        "prev_page_url": null,
     *        "to": 1,
     *        "total": 1
     *    }
     *}
     */
    public function seenAttendanceCount()
    {
        $validator = validator(request()->all(),[
            'group'=>'required|integer'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $group = LessonGroup::findOrFail(request('group'));
        $this->authorize('check_id', $group->teacher_id);
        $res = User::join('lesson_group_students as lgs','lgs.student_id','users.id')
            ->join('lesson_groups as lg','lg.id','lgs.lesson_group_id')
            ->join('parent_users as pu', 'pu.user_id','users.id')
            ->join('users as p', 'p.id','pu.parent_id')
            ->join('lesson_days as ld','ld.student_id','users.id')
            ->where('lg.id',$group->id)
            ->whereNull('lg.deleted_at')
            ->where('lgs.status','accept')
            ->groupBy(['users.id',
                'users.fullname',
                'p.id',
                'p.fullname'])
            ->paginate(10,['users.id as student_id',
                'users.fullname as student_fullname',
                'p.id as parent_id',
                'p.fullname as parent_fullname',
                DB::raw('COUNT(case when ld.parent_seen=1 then 1 else null end) as seen'),
                DB::raw('COUNT(case when ld.parent_seen=0 then 1 else null end) as not_seen')
                ]);
        return $this->sendSuccess($res);
    }

    /**
     * @api {get} lesson/lesson_day/seen_student_attendance Bir sagirdin valideyninin davamiyyeti izleme sayi
     * @apiGroup Teacher
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} group unique group id
     * @apiParam {Integer} student unique student id
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "student_id": 31,
     *        "student_fullname": "Sagirdov sagird",
     *        "parent_id": 32,
     *        "parent_fullname": "Sagirdin Valideyni",
     *        "seen": 0,
     *        "not_seen": 1
     *    }
     *}
     */
    public function seenStudentAttendanceCount()
    {
        $validator = validator(request()->all(),[
            'group'=>'required|integer',
            'student'=>'required|integer'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $group = LessonGroup::findOrFail(request('group'));
        $this->authorize('check_id', $group->teacher_id);
        $res = User::join('lesson_group_students as lgs','lgs.student_id','users.id')
            ->join('lesson_groups as lg','lg.id','lgs.lesson_group_id')
            ->join('parent_users as pu', 'pu.user_id','users.id')
            ->join('users as p', 'p.id','pu.parent_id')
            ->join('lesson_days as ld','ld.student_id','users.id')
            ->where('lg.id',$group->id)
            ->whereNull('lg.deleted_at')
            ->where('lgs.status','accept')
            ->where('users.id',request('student'))
            ->groupBy(['users.id',
                'users.fullname',
                'p.id',
                'p.fullname'])
            ->first(['users.id as student_id',
                'users.fullname as student_fullname',
                'p.id as parent_id',
                'p.fullname as parent_fullname',
                DB::raw('COUNT(case when ld.parent_seen=1 then 1 else null end) as seen'),
                DB::raw('COUNT(case when ld.parent_seen=0 then 1 else null end) as not_seen')
                ]);
        return $this->sendSuccess($res);
    }

    /**
     * @api {post} lesson/lesson_day add or update lesson day
     * @apiGroup Teacher
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} student unique user id
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Date} [date] default = now
     * @apiParam {Enum} type (in:absent,in_time,late,left_earlier)
     * @apiParam {Integer} [mark_home]
     * @apiParam {Integer} [mark_lesson]
     * @apiParam {String} [note_home]
     * @apiParam {String} [note_lesson]
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": {
     *          "inserted_id": 7
     *      }
     *  }
     */
    public function addOrUpdateLessonDayByTeacher()
    {
        $validator = validator(request()->all(),[
            'student'=>'required|integer|exists:users,id',
            'lesson'=>'required|integer',
            'date' => 'nullable|date_format:Y-m-d',
            'type'=>'required|in:absent,in_time,late,left_earlier',
            'mark_home'=>'nullable|integer|between:0,100',
            'mark_lesson'=>'nullable|integer|between:0,100',
            'note_home'=>'nullable|string',
            'note_lesson'=>'nullable|string',
            'reason'=>'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::with('lesson_group')->findOrFail(request('lesson'));
        $this->authorize('check_id', $lesson->lesson_group->teacher_id);
        $date = request('date') ?: now()->format('Y-m-d');
        $lesson_day = LessonDay::where('student_id',request('student'))
            ->where('lesson_id',request('lesson'))
            ->where('date',$date)->first();
        if(!$lesson_day){
            $lesson_day = new LessonDay;
            $lesson_day->student_id = request('student');
            $lesson_day->lesson_id = request('lesson');
            $lesson_day->date = $date;
        }
        $lesson_day->type = request('type');
        $lesson_day->mark_home = request('mark_home');
        $lesson_day->mark_lesson = request('mark_lesson');
        $lesson_day->note_home = request('note_home');
        $lesson_day->note_lesson = request('note_lesson');
        $lesson_day->reason = request('reason');
        $lesson_day->save();
        return $this->sendSuccess(['inserted_id'=>$lesson_day->id]);
    }

    /**
     * @api {post} lesson/lesson_day/home_work add home work mark
     * @apiGroup Teacher
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Array} students unique users id
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Date} [date] default = now
     * @apiParam {Integer} mark
     * @apiParam {String} [note]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function home_work()
    {
        $validator = validator(request()->all(),[
            'students'=>'required|array',
            'students.*'=>'required|integer|exists:users,id',
            'lesson'=>'required|integer',
            'date' => 'nullable|date_format:Y-m-d',
            'mark'=>'required|integer|between:0,100',
            'note'=>'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::with('lesson_group')->findOrFail(request('lesson'));
        $this->authorize('check_id', $lesson->lesson_group->teacher_id);
        $date = request('date') ?: now()->format('Y-m-d');
        foreach(request('students') as $student_id){
            $lesson_day = LessonDay::where('student_id',$student_id)
                ->where('lesson_id',request('lesson'))
                ->where('date',$date)->first();
            if(!$lesson_day){
                $lesson_day = new LessonDay;
                $lesson_day->student_id = $student_id;
                $lesson_day->lesson_id = request('lesson');
                $lesson_day->date = $date;
            }
            $lesson_day->mark_home = request('mark');
            $lesson_day->note_home = request('note');
            $lesson_day->save();
        }
        return $this->sendSuccess();
    }

    /**
     * @api {post} lesson/lesson_day/lesson_work add lesson work mark
     * @apiGroup Teacher
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Array} students unique users id
     * @apiParam {Integer} lesson unique lesson id
     * @apiParam {Date} [date] default = now
     * @apiParam {Integer} mark
     * @apiParam {String} [note]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function lesson_work()
    {
        $validator = validator(request()->all(),[
            'students'=>'required|array',
            'students.*'=>'required|integer|exists:users,id',
            'lesson'=>'required|integer',
            'date' => 'nullable|date_format:Y-m-d',
            'mark'=>'required|integer|between:0,100',
            'note'=>'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::with('lesson_group')->findOrFail(request('lesson'));
        $this->authorize('check_id', $lesson->lesson_group->teacher_id);
        $date = request('date') ?: now()->format('Y-m-d');
        foreach(request('students') as $student_id){
            $lesson_day = LessonDay::where('student_id',$student_id)
                ->where('lesson_id',request('lesson'))
                ->where('date',$date)->first();
            if(!$lesson_day){
                $lesson_day = new LessonDay;
                $lesson_day->student_id = $student_id;
                $lesson_day->lesson_id = request('lesson');
                $lesson_day->date = $date;
            }
            $lesson_day->mark_lesson = request('mark');
            $lesson_day->note_lesson = request('note');
            $lesson_day->save();
        }
        return $this->sendSuccess();
    }

    /**
     * @api {delete} lesson/lesson_day/:id delete lesson day
     * @apiGroup Teacher
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson_day id
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function deleteLessonDay($id)
    {
        $lesson_day = LessonDay::with('lesson.lesson_group')->findOrFail($id);
        $this->authorize('check_id',$lesson_day->lesson->lesson_group->teacher_id);
        $lesson_day->delete();
        return $this->sendSuccess();
    }

    /**
     * @api {get} lesson/lesson_day/seen_student_attendance_by_lesson Bir sagirdin valideyninin davamiyyeti izleme sayi lesson id-ye gore
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
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "student_id": 31,
     *        "student_fullname": "Sagirdov sagird",
     *        "parent_id": 32,
     *        "parent_fullname": "Sagirdin Valideyni",
     *        "seen": 0,
     *        "not_seen": 1
     *    }
     *}
     */
    public function seenStudentAttendanceCountByLessonId()
    {
        $validator = validator(request()->all(),[
            'lesson'=>'required|integer',
            'student'=>'required|integer'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::findOrFail(request('lesson'));
        $group = LessonGroup::find($lesson->lesson_group_id);
        $this->authorize('check_id', $group->teacher_id);
        $res = User::join('lesson_group_students as lgs','lgs.student_id','users.id')
            ->join('lesson_groups as lg','lg.id','lgs.lesson_group_id')
            ->join('parent_users as pu', 'pu.user_id','users.id')
            ->join('users as p', 'p.id','pu.parent_id')
            ->join('lesson_days as ld','ld.student_id','users.id')
            ->where('lg.id',$group->id)
            ->whereNull('lg.deleted_at')
            ->where('lgs.status','accept')
            ->where('users.id',request('student'))
            ->groupBy(['users.id',
                'users.fullname',
                'p.id',
                'p.fullname'])
            ->first(['users.id as student_id',
                'users.fullname as student_fullname',
                'p.id as parent_id',
                'p.fullname as parent_fullname',
                DB::raw('COUNT(case when ld.parent_seen=1 then 1 else null end) as seen'),
                DB::raw('COUNT(case when ld.parent_seen=0 then 1 else null end) as not_seen')
                ]);
        return $this->sendSuccess($res);
    }

    /**
     * @api {get} lesson/lesson_day/attendance/statistic Sagirdin derslerdeki davamiyyetinin sayi
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
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "absent": 1,
     *        "in_time": 0,
     *        "late": 1,
     *        "left_earlier": 0
     *    }
     *}
     */
    public function attendanceStatistic()
    {
        $validator = validator(request()->all(),[
            'lesson'=>'required|integer',
            'student'=>'required|integer'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::findOrFail(request('lesson'));
        $group = LessonGroup::find($lesson->lesson_group_id);
        $this->authorize('check_id', $group->teacher_id);
        $types = LessonDay::where('student_id',request('student'))
            ->whereIn('lesson_id',$group->lessons)
            ->pluck('type');
        $count = LessonDayHelper::typesCount($types);
        return $this->sendSuccess($count);
    }
    
    /**
     * @api {get} lesson/lesson_day/seen_attendance_by_lesson Valideynlerin davamiyyeti izleme sayi (lesson_id-ye gore)
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
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "student_id": 31,
     *                "student_fullname": "Sagirdov sagird",
     *                "parent_id": 32,
     *                "parent_fullname": "Sagirdin Valideyni",
     *                "seen": 0,
     *                "not_seen": 1
     *            }
     *        ],
     *        "first_page_url": "api.egundelik.com/lesson/lesson_day/seen_attendance?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "api.egundelik.com/lesson/lesson_day/seen_attendance?page=1",
     *        "next_page_url": null,
     *        "path": "api.egundelik.com/lesson/lesson_day/seen_attendance",
     *        "per_page": 10,
     *        "prev_page_url": null,
     *        "to": 1,
     *        "total": 1
     *    }
     *}
     */
    public function seenAttendanceCountByLessonId()
    {
        $validator = validator(request()->all(),[
            'lesson'=>'required|integer'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $lesson = Lesson::findOrFail(request('lesson'));
        $group = LessonGroup::find($lesson->lesson_group_id);
        $this->authorize('check_id', $group->teacher_id);
        $res = User::join('lesson_group_students as lgs','lgs.student_id','users.id')
            ->join('lesson_groups as lg','lg.id','lgs.lesson_group_id')
            ->join('parent_users as pu', 'pu.user_id','users.id')
            ->join('users as p', 'p.id','pu.parent_id')
            ->join('lesson_days as ld','ld.student_id','users.id')
            ->where('lg.id',$group->id)
            ->whereNull('lg.deleted_at')
            ->where('lgs.status','accept')
            ->groupBy(['users.id',
                'users.fullname',
                'p.id',
                'p.fullname'])
            ->paginate(10,['users.id as student_id',
                'users.fullname as student_fullname',
                'p.id as parent_id',
                'p.fullname as parent_fullname',
                DB::raw('COUNT(case when ld.parent_seen=1 then 1 else null end) as seen'),
                DB::raw('COUNT(case when ld.parent_seen=0 then 1 else null end) as not_seen')
                ]);
        return $this->sendSuccess($res);
    }


    public function getLogs($columns)
    {
        $validator = validator(request()->all(),[
            'student' => 'required|integer',
            'lesson' => 'required|integer',
            'start_date' => 'nullable|date_format:d-m-Y',
            'end_date' => 'nullable|date_format:d-m-Y'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson = Lesson::with('lesson_group.lessons')->findOrFail(request('lesson'));
        $lessons_id = $lesson->lesson_group->lessons->pluck('id');
        $start_date = request('start_date') ? Carbon::parse(request('start_date')) : null;
        $end_date = request('end_date') ? Carbon::parse(request('end_date')) : null;
        $log = LessonDay::whereIn('lesson_id',$lessons_id)
            ->where('student_id',request('student'))
            ->when($start_date, function($q) use($start_date){
                return $q->where('date','>=',$start_date);
            })
            ->when($end_date, function($q) use($end_date){
                return $q->where('date','<=',$end_date);
            })
            ->paginate(10,$columns);
        return $this->sendSuccess($log);
    }
}

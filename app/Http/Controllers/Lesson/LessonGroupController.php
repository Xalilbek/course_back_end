<?php

namespace App\Http\Controllers\Lesson;

use App\Helpers\Lesson\LessonGroupHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonDay;
use App\Models\Lesson\LessonGroup;
use App\Models\Lesson\LessonGroupStudent;
use App\User;
use Illuminate\Support\Facades\DB;

class LessonGroupController extends Controller
{
    /**
     * @api {get} /lesson/group/:id show lesson group by id
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson group id
     * 
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": {
     *          "id": 2,
     *          "name": "Qrup 2",
     *          "subject_id": 1,
     *          "teacher_id": 2,
     *          "created_at": "2020-03-22 16:07:36",
     *          "updated_at": "2020-03-22 16:07:36",
     *          "lessons": [
     *              {
     *                  "id": 3,
     *                  "lesson_group_id": 2,
     *                  "week_day": 3,
     *                  "time": "10:00:00",
     *                  "lesson_group_students": [
     *                      {
     *                          "id": 9,
     *                          "lesson_group_id": 2,
     *                          "student_id": 29,
     *                          "status": "none",
     *                          "pivot": {
     *                              "lesson_id": 3,
     *                              "lesson_group_student_id": 9
     *                          },
     *                          "student": {
     *                              "id": 29,
     *                              "fullname": "Quliyev Ehmed",
     *                              "avatar": "https://api.egundelik.com/images/avatars/~~~4MW9PiTl9O1584892726.jpg"
     *                          }
     *                      }
     *                  ]
     *              }
     *          ]
     *      }
     *  }
     */
    public function showLessonGroup($id)
    {
        $group = LessonGroup::with(['lessons'=>function($q){
            $q->select(['id','lesson_group_id','week_day','time'])
                ->with(['lesson_group_students'=>function($q){
                    $q->select(['lesson_group_students.id','lesson_group_id','student_id','status'])
                        ->with(['student'=>function($q){
                            $q->select(['id','fullname','avatar']);
                        }]);
                    }]);
                }
            ])->findOrFail($id);
        $this->authorize('check_id',$group->teacher_id);
        return $this->sendSuccess($group);
    }
    
    /**
     * @api {post} /lesson/group Create group and lessons
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {String} name
     * @apiParam {Integer} subject unique subject id
     * @apiParam {Object[]} lessons
     * @apiParam {Integer} lessons.week_day
     * @apiParam {String} lessons.time format(H:i)
     * 
     * @apiParamExample {json} Request-Example:
     * {
     *      "name":"qrup 2",
     *       "subject":1,
     *       "lessons":[
     *           {
     *           "week_day":2,
     *           "time":"15:00"
     *           },
     *           {
     *           "week_day":5,
     *           "time":"17:00"
     *           }
     *       ]
     *   }
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":{
     *  "inserted_id":3
     * }
     * }
     */
    public function addLessonGroup()
    {
        $validator = validator(request()->all(), [
            'name' => 'required',
            'subject' => 'required|integer|exists:subjects,id',
            'lessons' => 'required|array',
            'lessons.*.week_day' => 'required|integer|between:1,7',
            'lessons.*.time' => 'required|date_format:H:i'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $lesson_group = new LessonGroup;
            $lesson_group->name = request('name');
            $lesson_group->subject_id = request('subject');
            $lesson_group->teacher_id = auth()->id();
            $lesson_group->save();

            $lessonObj = [];
            foreach (request('lessons') as $lesson) {
                if(LessonGroupHelper::teacherHasLesson($lesson)){
                    DB::rollBack();
                    return $this->sendError(__('message.basqa_dersle_ust_uste_dusur'));
                }
                $lessonObj[] = new Lesson($lesson);
            }
            $lesson_group->lessons()->saveMany($lessonObj);

            DB::commit();
            return $this->sendSuccess(['inserted_id' => $lesson_group->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError([], $ex->getMessage());
        }
    }

    /**
     * @api {put} /lesson/group/:id Update group
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson group id
     * @apiParam {String} name
     * @apiParam {Integer} subject unique subject id
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function updateLessonGroup($id)
    {
        $validator = validator(request()->all(), [
            'name' => 'required',
            'subject' => 'required|integer|exists:subjects,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson_group = LessonGroup::findOrFail($id);
        $this->authorize('check_id',$lesson_group->teacher_id);
        $lesson_group->name = request('name');
        $lesson_group->subject_id = request('subject');
        $lesson_group->save();
        return $this->sendSuccess();
    }

    /**
     * @api {delete} /lesson/group/:id Delete group
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson group id
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function deleteLessonGroup($id)
    {
        $lesson_group = LessonGroup::findOrFail($id);
        $this->authorize('check_id',$lesson_group->teacher_id);
        $lesson_group->delete();
        return $this->sendSuccess();
    }

        /**
         * @api {post} /lesson/search_groups_by_week_day/:week_day search lesson by week day
         * @apiGroup Student
         * 
         * @apiHeaderExample {json} Header-Example:
         * {
         *    "Accept":"application/json",
         *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
         * "Content-lang":"az"
         * }
         * 
         * @apiParam {Integer} week_day
         * @apiSuccessExample Success-Response:
         *     {
         *    "status": true,
         *    "data": {
         *        "current_page": 1,
         *        "data": [
         *            {
         *                "id": 1,
         *                "lesson_group_id": 1,
         *                "week_day": 2,
         *                "time": "10:00:00",
         *                "created_at": "2020-03-08 12:03:15",
         *                "updated_at": "2020-03-08 12:03:15",
         *                "lesson_group": {
         *                    "id": 1,
         *                    "name": "Qrup 1",
         *                    "subject_id": 2,
         *                    "teacher_id": 6,
         *                    "created_at": "2020-03-08 12:03:15",
         *                    "updated_at": "2020-03-08 12:03:15"
         *                }
         *            }
         *        ],
         *        "first_page_url": "https://api.egundelik.com/lesson/search_groups_by_week_day/2?page=1",
         *        "from": 1,
         *        "last_page": 1,
         *        "last_page_url": "https://api.egundelik.com/lesson/search_groups_by_week_day/2?page=1",
         *        "next_page_url": null,
         *        "path": "https://api.egundelik.com/lesson/search_groups_by_week_day/2",
         *        "per_page": 20,
         *        "prev_page_url": null,
         *        "to": 1,
         *        "total": 1
         *    }
         *}
     */
    public function searchGroupsByWeekDay($week_day){
        $validator = validator(['week_day'=>$week_day],[
            'week_day' => 'required|integer|between:1,7'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        $groups = Lesson::with('lesson_group')->where('week_day',$week_day)->paginate(20);
        return $this->sendSuccess($groups);
    }

    /**
     * @api {get} /lesson/student/search Sagirdin qruplarinda olan sagirdler ve muellimlerin axtarisi
     * @apiGroup Student
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {String} [username]
     * @apiSuccessExample Success-Response:
     *  {
     *      "status":true,
     *      "data":{
     *      "teachers": [
     *          {
     *              "id":1,
     *              "fullname": "Professor",
     *              "avatar": "https://api.egundelik.com/images/avatars/_downloadfiles_wallpapers_1366_768_ford_shelby_gt500_car_10107kOqsAcsPTd1584192137.jpg",
     *              "name": "Fizika"
     *          }
     *      ],
     *      "students": [
     *          {
     *              "id":2,
     *              "fullname": "Sagirdov sagird",
     *              "avatar": "https://api.egundelik.com/images/avatars/~~~YdvLp1gU6V1584192763.jpg",
     *              "name": "Qrup 1"
     *          }
     *      ]
     *  }
     * }
     */
    public function searchTeachersAndStudents()
    {
        $validator = validator(request()->all(), [
            'username' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $groups_id = LessonGroupStudent::where('student_id',auth()->id())
            ->where('lesson_group_students.status','accept')
            ->pluck('lesson_group_id');

        $teachers = User::join('lesson_groups as lg', 'users.id','lg.teacher_id')
                    ->join('subjects as s','s.id','lg.subject_id')
                    ->whereIn('lg.id',$groups_id)
                    ->whereNull('lg.deleted_at');

        $students = User::join('lesson_group_students as lgs', 'users.id', 'lgs.student_id')
                    ->join('lesson_groups as lg','lg.id','lgs.lesson_group_id')
                    ->whereIn('lesson_group_id',$groups_id)
                    ->whereNull('lg.deleted_at')
                    ->where('lgs.status','accept')
                    ->where('users.id','!=',auth()->id());

        if(request('username')){
            $teachers = $teachers->where('users.fullname','like','%'.request('username').'%');
            $students = $students->where('users.fullname','like','%'.request('username').'%');
        }
        return $this->sendSuccess(['teachers'=>$teachers->get(['users.id','users.fullname','users.avatar','s.name']),
                'students'=>$students->get(['users.id','users.fullname','users.avatar','lg.name'])]);
    }

    /**
     * @api {get} /lesson/teacher/search Muellimin qruplarinda olar sagirdlerin ve valideynlerin axtarisi
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {String} [username]
     * @apiSuccessExample Success-Response:
     *          {
     *          "status": true,
     *          "data": {
     *              "students": [
     *                  {
     *                      "id": 4,
     *                      "fullname": "Sagirdov sagird",
     *                      "avatar": "https://api.egundelik.com/images/avatars/~~~YdvLp1gU6V1584192763.jpg",
     *                      "name": "Qrup 1"
     *                  }
     *              ],
     *              "parents": [
     *                  {
     *                      "id": 5,
     *                      "fullname": "Sagirdin Valideyni",
     *                      "student_name": "Sagirdov sagird",
     *                      "student_id": 4,
     *                      "avatar": null
     *                  }
     *              ]
     *          }
     *      }
     */
    public function searchParentsAndStudents()
    {
        $validator = validator(request()->all(), [
            'username' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $students = User::join('lesson_group_students as lgs', 'users.id', 'lgs.student_id')
            ->join('lesson_groups as lg', 'lg.id', 'lgs.lesson_group_id')
            ->where('lgs.status','accept')
            ->where('lg.teacher_id', auth()->id())
            ->whereNull('lg.deleted_at');
        $students_id = $students->pluck('users.id');
        $parents = User::join('parent_users as pu', 'users.id', 'pu.parent_id')
            ->join('users as s', 's.id', 'pu.user_id')
            ->whereIn('pu.user_id', $students_id);

        if (request('username')) {
            $parents = $parents->where('users.fullname', 'like', '%' . request('username') . '%');
            $students = $students->where('fullname', 'like', '%' . request('username') . '%');
        }

        $parents = $parents->get(['users.id', 'users.fullname', 's.fullname as student_name', 's.id as student_id', 'users.avatar']);
        $students = $students->get(['users.id', 'fullname', 'avatar', 'lg.name']);
        return $this->sendSuccess(['students' => $students, 'parents' => $parents]);
    }

    /**
     * @api {get} /lesson/group/show/active_subjects Sagirdin hazirliq kecdiyi fennler
     * @apiGroup Student
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": [
     *        {
     *            "id": 1,
     *            "name": "Riyaziyyat"
     *        }
     *    ]
     *}
     */
    public function showActiveSubjects()
    {
        $user = auth()->user();
        if($user->user_type == 'student'){
            $groups = LessonGroupStudent::with('lesson_group.subject')
                ->where('student_id',$user->id)
                ->where('status','accept')
                ->get();
        }
        else{
            $student_id = DB::table('parent_users')->where('parent_id',$user->id)->value('user_id');
            $groups = LessonGroupStudent::with('lesson_group.subject')
                ->where('student_id',$student_id)
                ->where('status','accept')
                ->get();
        }
        $subjects = $groups->pluck('lesson_group.subject');
        $res = [];
        foreach($subjects as $subject){
            $res[] = [
                'id' => $subject->id,
                'name' => $subject->name
            ];
        }
        return $this->sendSuccess($res);
    }

    /**
     * @api {get} /lesson/group/reyting/subject/:id show students reytinq
     * @apiGroup Student
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * @apiParam {Integer} id unique subject id
     * @apiParam {Enum} filter in:(day,week,all) default = all
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "student_id": 31,
     *                "fullname": "Sagirdov sagird",
     *                "total_mark": 80
     *            },
     *            {
     *                "student_id": 30,
     *                "fullname": "Quliyev Ehmed",
     *                "total_mark": 60
     *            }
     *        ],
     *        "first_page_url": "http://localhost:8000/lesson/teacher/group/reyting?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "http://localhost:8000/lesson/teacher/group/reyting?page=1",
     *        "next_page_url": null,
     *        "path": "http://localhost:8000/lesson/teacher/group/reyting",
     *        "per_page": 20,
     *        "prev_page_url": null,
     *        "to": 2,
     *        "total": 2
     *    }
     *}
     */
    public function showReytingBySubjectId($id)
    {
        $validator = validator(request()->all(), [
            'filter' => 'nullable|in:day,week,all'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $user = auth()->user();
        if($user->user_type == 'student'){
            $group = LessonGroup::with('lessons:id,lesson_group_id')
                ->whereHas('lesson_group_students', function($q) use($user){
                    $q->where('student_id',$user->id)->where('status','accept');
                })
                ->where('subject_id',$id)
                ->first(['id']);
        }
        else{
            $student_id = DB::table('parent_users')->where('parent_id',$user->id)->value('user_id');
            $group = LessonGroup::with('lessons:id,lesson_group_id')
                ->whereHas('lesson_group_students', function($q) use($student_id){
                    $q->where('student_id',$student_id)->where('status','accept');
                })
                ->where('subject_id',$id)
                ->first(['id']);
        }
        if(!isset($group->lessons)){
            return $this->sendSuccess();
        }
        $lessons_id = $group->lessons->pluck('id');
        $reyting = LessonDay::whereIn('lesson_id',$lessons_id)
            ->join('users as u', 'u.id', 'lesson_days.student_id')
            ->when((request('filter')=='day'), function($q){
                $q->whereDate('date',now());
            })
            ->when((request('filter')=='week'), function($q){
                $q->whereDate('date','>=',now()->subDays(6));
            })
            ->groupBy(['u.id', 'u.fullname'])
            ->orderByDesc('total_mark')
            ->get(['u.id as student_id', 'u.fullname',
                DB::raw(
                    'CAST(SUM(
                        (CASE WHEN mark_home IS NULL THEN 0 ELSE mark_home END)
                            + 
                        (CASE WHEN mark_lesson IS NULL THEN 0 ELSE mark_lesson END)
                    ) as int) as total_mark'
                )
            ]);
        return $this->sendSuccess($reyting);
    }

    /**
     * @api {get} /lesson/teacher/group/reyting Reyting muellim teref
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * @apiParam {Integer} group unique group id
     * @apiParam {Integer} subject unique subject id
     * @apiParam {Enum} filter in:(day,week,all) default = all
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "student_id": 31,
     *                "fullname": "Sagirdov sagird",
     *                "total_mark": 80
     *            },
     *            {
     *                "student_id": 30,
     *                "fullname": "Quliyev Ehmed",
     *                "total_mark": 60
     *            }
     *        ],
     *        "first_page_url": "http://localhost:8000/lesson/teacher/group/reyting?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "http://localhost:8000/lesson/teacher/group/reyting?page=1",
     *        "next_page_url": null,
     *        "path": "http://localhost:8000/lesson/teacher/group/reyting",
     *        "per_page": 20,
     *        "prev_page_url": null,
     *        "to": 2,
     *        "total": 2
     *    }
     *}
     */
    public function getTeacherReytingStudents()
    {
        $validator = validator(request()->all(), [
            'group' => 'nullable|integer',
            'subject' => 'nullable|integer',
            'filter' => 'nullable|in:day,week,all'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $user = auth()->user();
        $groups = LessonGroup::with('lessons:id,lesson_group_id')
            ->where('teacher_id', $user->id)
            ->when(request('group'), function($q){
                $q->where('id',request('group'));
            }, function($q){
                $q->when(request('subject'), function($q){
                    $q->where('subject_id', request('subject'));
                });
            })
            ->get(['id']);
        if(!$groups){
            return $this->sendSuccess();
        }
        $lessons_id = [];
        foreach($groups as $group){
            foreach($group->lessons as $lesson){
                $lessons_id[] = $lesson->id;
            }
        }
        $reyting = LessonDay::whereIn('lesson_id',$lessons_id)
            ->join('users as u', 'u.id', 'lesson_days.student_id')
            ->when((request('filter')=='day'), function($q){
                $q->whereDate('date',now());
            })
            ->when((request('filter')=='week'), function($q){
                $q->whereDate('date','>=',now()->subDays(6));
            })
            ->groupBy(['u.id', 'u.fullname'])
            ->orderByDesc('total_mark')
            ->paginate(20,['u.id as student_id', 'u.fullname',
                DB::raw(
                    'CAST(SUM(
                        (CASE WHEN mark_home IS NULL THEN 0 ELSE mark_home END)
                            + 
                        (CASE WHEN mark_lesson IS NULL THEN 0 ELSE mark_lesson END)
                    ) as int) as total_mark'
                )
            ]);
        return $this->sendSuccess($reyting);
    }

    /**
     * @api {get} /lesson/teacher/group Muellimin oz qruplari ve kecdiyi fennler
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "current_page": 1,
     *        "data": [
     *            {
     *                "id": 1,
     *                "name": "Qrup 1",
     *                "subject_id": 1,
     *                "subject": {
     *                    "id": 1,
     *                    "name": "Riyaziyyat"
     *                }
     *            },
     *            {
     *                "id": 3,
     *                "name": "Qrup 2",
     *                "subject_id": 2,
     *                "subject": {
     *                    "id": 2,
     *                    "name": "Fizika"
     *                }
     *            }
     *        ],
     *        "first_page_url": "http://localhost:8000/lesson/teacher/group?page=1",
     *        "from": 1,
     *        "last_page": 1,
     *        "last_page_url": "http://localhost:8000/lesson/teacher/group?page=1",
     *        "next_page_url": null,
     *        "path": "http://localhost:8000/lesson/teacher/group",
     *        "per_page": 20,
     *        "prev_page_url": null,
     *        "to": 2,
     *        "total": 2
     *    }
     *}
     */
    public function getTeacherGroupsAndLessons()
    {
        $groups = LessonGroup::with('subject:id,name')->where('teacher_id', auth()->id())->paginate(20,['id','name','subject_id']);
        return $this->sendSuccess($groups);
    }

    /**
     * @api {get} /lesson/teacher/group/lesson/:id show group by lesson_id
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
     *  {
     *      "status": true,
     *      "data": {
     *          "id": 2,
     *          "name": "Qrup 2",
     *          "subject_id": 1,
     *          "teacher_id": 2,
     *          "created_at": "2020-03-22 16:07:36",
     *          "updated_at": "2020-03-22 16:07:36",
     *          "lessons": [
     *              {
     *                  "id": 3,
     *                  "lesson_group_id": 2,
     *                  "week_day": 3,
     *                  "time": "10:00:00",
     *                  "lesson_group_students": [
     *                      {
     *                          "id": 9,
     *                          "lesson_group_id": 2,
     *                          "student_id": 29,
     *                          "status": "none",
     *                          "pivot": {
     *                              "lesson_id": 3,
     *                              "lesson_group_student_id": 9
     *                          },
     *                          "student": {
     *                              "id": 29,
     *                              "fullname": "Quliyev Ehmed",
     *                              "avatar": "https://api.egundelik.com/images/avatars/~~~4MW9PiTl9O1584892726.jpg"
     *                          }
     *                      }
     *                  ]
     *              }
     *          ]
     *      }
     *  }
     */
    public function showLessonGroupByLessonId($id)
    {
        $group = LessonGroup::with(['lessons'=>function($q){
            $q->select(['id','lesson_group_id','week_day','time'])
                ->with(['lesson_group_students'=>function($q){
                    $q->select(['lesson_group_students.id','lesson_group_id','student_id','status'])
                        ->with(['student'=>function($q){
                            $q->select(['id','fullname','avatar']);
                        }]);
                    }]);
                }
            ])
            ->whereHas('lessons',function($q) use($id){
                $q->where('lessons.id',$id);
            })
            ->firstOrFail();
        $this->authorize('check_id',$group->teacher_id);
        return $this->sendSuccess($group);
    }

    /**
     * @api {put} /lesson/teacher/group/lesson/{id} Update group by lesson id
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
     * @apiParam {String} name
     * @apiParam {Integer} subject unique subject id
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function updateLessonGroupByLessonId($id)
    {
        $validator = validator(request()->all(), [
            'name' => 'required',
            'subject' => 'required|integer|exists:subjects,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        $lesson_group = LessonGroup::whereHas('lessons',function($q) use($id){
                            $q->where('lessons.id',$id);
                        })
                        ->firstOrFail();
        $this->authorize('check_id',$lesson_group->teacher_id);
        $lesson_group->name = request('name');
        $lesson_group->subject_id = request('subject');
        $lesson_group->save();
        return $this->sendSuccess();
    }

    /**
     * @api {delete} /lesson/group/:id Delete group
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique lesson group id
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function deleteLessonGroupByLessonId($id)
    {
        $lesson_group = LessonGroup::whereHas('lessons',function($q) use($id){
                            $q->where('lessons.id',$id);
                        })
                        ->firstOrFail();
        $this->authorize('check_id',$lesson_group->teacher_id);
        $lesson_group->delete();
        return $this->sendSuccess();
    }

    /**
     * @api {get} /lesson/group/show/lesson/:id Qrupda olar muellimler ve telebeler
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
     * 
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": {
     *        "teacher": {
     *            "id": 3,
     *            "fullname": "Məmmədov Məmməd"
     *        },
     *        "students": [
     *            {
     *                "id": 31,
     *                "fullname": "Sagirdov sagird"
     *            }
     *        ]
     *    }
     *}
     */
    public function showGroupTeacherAndStudentsByLessonId($id)
    {
        $lesson = Lesson::with(['lesson_group.teacher:id,fullname',
                'lesson_group_students'=>function($q){
                    $q->with('student:id,fullname')->where('status','accept');
                }
            ])->findOrFail($id);
        $students = $lesson->lesson_group_students->pluck('student');
        $teacher = $lesson->lesson_group->teacher;
        return $this->sendSuccess(['teacher'=>$teacher,'students'=>$students]);
    }

    /**
     * @api {get} /lesson/group/show/lessons/lesson/:id show lessons
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
     * 
     * @apiSuccessExample Success-Response:
     *{
     *    "status": true,
     *    "data": [
     *        {
     *            "id": 1,
     *            "lesson_group_id": 2,
     *            "week_day": 1,
     *            "time": "10:00:00",
     *            "created_at": "2020-04-20 15:28:02",
     *            "updated_at": "2020-04-20 15:28:02"
     *        },
     *        {
     *            "id": 2,
     *            "lesson_group_id": 2,
     *            "week_day": 3,
     *            "time": "10:00:00",
     *            "created_at": "2020-04-20 15:28:02",
     *            "updated_at": "2020-04-20 15:28:02"
     *        },
     *        {
     *            "id": 3,
     *            "lesson_group_id": 2,
     *            "week_day": 5,
     *            "time": "14:00:00",
     *            "created_at": "2020-04-20 15:28:02",
     *            "updated_at": "2020-04-20 15:28:02"
     *        }
     *    ]
     *}
     */
    public function showGroupLessonsByLessonId($id)
    {
        $lesson = Lesson::with(['lesson_group.lessons'])->findOrFail($id);
        return $this->sendSuccess($lesson->lesson_group->lessons);
    }
}

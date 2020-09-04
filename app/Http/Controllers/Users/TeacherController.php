<?php

namespace App\Http\Controllers\Users;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Users\TeacherUser;
use App\User;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * @api {get} /teacher All Teachers and filter
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
     * @apiParam {Integer} [subject] unique subject id
     * @apiParam {Integer} [sector] unique language sector id
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": {
     *          "current_page": 1,
     *          "data": [
     *              {
     *                  "id": 2,
     *                  "fullname": "Ziya Ziyali",
     *                  "avatar": "https://api.egundelik.com/images/avatars/Capture001WHJRC09psd1584428141.png",
     *                  "lesson_groups": [
     *                      {
     *                          "id": 1,
     *                          "name": "qrup 1",
     *                          "subject_id": 1,
     *                          "teacher_id": 2,
     *                          "created_at": "2020-03-18 07:43:32",
     *                          "updated_at": "2020-03-18 07:43:32",
     *                          "lessons": [
     *                              {
     *                                  "id": 1,
     *                                  "lesson_group_id": 1,
     *                                  "week_day": 2,
     *                                  "time": "15:00:00",
     *                                  "created_at": "2020-03-18 07:43:32",
     *                                  "updated_at": "2020-03-18 07:43:32"
     *                              },
     *                              {
     *                                  "id": 2,
     *                                  "lesson_group_id": 1,
     *                                  "week_day": 5,
     *                                  "time": "17:00:00",
     *                                  "created_at": "2020-03-18 07:43:33",
     *                                  "updated_at": "2020-03-18 07:43:33"
     *                              }
     *                          ],
     *                          "subject": {
     *                              "id": 1,
     *                              "name": "Riyaziyyat",
     *                              "created_at": "2020-03-17 06:46:04",
     *                              "updated_at": "2020-03-17 06:46:04",
     *                              "deleted_at": null
     *                          }
     *                      },
     *                      {
     *                          "id": 2,
     *                          "name": "qrup 2",
     *                          "subject_id": 1,
     *                          "teacher_id": 2,
     *                          "created_at": "2020-03-18 07:43:53",
     *                          "updated_at": "2020-03-18 07:43:53",
     *                          "lessons": [
     *                              {
     *                                  "id": 3,
     *                                  "lesson_group_id": 2,
     *                                  "week_day": 3,
     *                                  "time": "15:00:00",
     *                                  "created_at": "2020-03-18 07:43:53",
     *                                  "updated_at": "2020-03-18 07:43:53"
     *                              },
     *                              {
     *                                  "id": 4,
     *                                  "lesson_group_id": 2,
     *                                  "week_day": 6,
     *                                  "time": "17:00:00",
     *                                  "created_at": "2020-03-18 07:43:53",
     *                                  "updated_at": "2020-03-18 07:43:53"
     *                              }
     *                          ],
     *                          "subject": {
     *                              "id": 1,
     *                              "name": "Riyaziyyat",
     *                              "created_at": "2020-03-17 06:46:04",
     *                              "updated_at": "2020-03-17 06:46:04",
     *                              "deleted_at": null
     *                          }
     *                      }
     *                  ]
     *              }
     *          ],
     *          "first_page_url": "https://api.egundelik.com/teacher?page=1",
     *          "from": 1,
     *          "last_page": 1,
     *          "last_page_url": "https://api.egundelik.com/teacher?page=1",
     *          "next_page_url": null,
     *          "path": "https://api.egundelik.com/teacher",
     *          "per_page": 10,
     *          "prev_page_url": null,
     *          "to": 1,
     *          "total": 1
     *      }
     *  }
     */
    public function allTeachersAndFilter()
    {    
        $validator = validator(request()->all(),[
            'username'=>'nullable|string',
            'subject'=>'nullable|integer',
            'sector'=>'nullable|integer'
        ]);
        if($validator->fails()){
            return $this->sendError(validator()->errors()->toArray());
        }
        $teachers = User::with([
            'lesson_groups'=>function($q){
                $q->with(['lessons','subject']);
            }
        ])
        ->onlyTeachers()
        ->when((request('username')), function($q) {
            return $q->where('fullname', 'like', '%' . request('username') . '%');
        })
        ->when((request('subject')), function($q) {
            return $q->whereHas('lesson_groups', function ($q) {
                $q->where('lesson_groups.subject_id', request('subject'));
            });
        })
        ->when((request('sector')), function($q) {
            return $q->whereHas('language_sectors', function ($q) {
                $q->where('language_sectors.id', request('sector'));
            });
        });

        return $this->sendSuccess($teachers->paginate(10,['id','fullname','avatar']));
    }

     /**
     * @api {post} /teacher/register Register teacher
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {String} fullname
     * @apiParam {File} [avatar]
     * @apiParam {Date} birth (format Y-m-d)
     * @apiParam {Enum} gender (in m,f)
     * @apiParam {Number} phone
     * @apiParam {String} email
     * @apiParam {String} address
     * @apiParam {Array} education_levels unique educations id
     * @apiParam {Array} universities unique universities id
     * @apiParam {Array} language_sectors unique language_sectors id
     * @apiParam {Array} subjects unique subjects id
     * @apiParam {Integer} school unique school_id
     * 
     * @apiParamExample {json} Request-Example:
     * {
     *      "fullname":"Ziya Ziyali",
     *      "birth":"1970-10-10",
     *      "gender":"m",
     *      "phone":"1234567899",
     *      "email":"test@test.com",
     *      "address":"Yasamal",
     *      "education_levels":[1,2],
     *      "universities":[1,2],
     *      "language_sectors":[1,2],
     *      "subjects":[1],
     *      "school":2,
     *      "avatar":"FILE"
     *  }
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status": true,
     *  "data": {
     *      "inserted_id": 2
     *  }
     * }
     */
    public function registerTeacher()
    {
        $validator = validator(request()->all(), [
            'fullname' => 'required|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png|max:10000',
            'birth' => 'required|date_format:Y-m-d|before:today',
            'gender' => 'required|in:m,f',
            'phone' => 'required|numeric|min:10',
            'email' => 'required|email|unique:users',
            'address' => 'required|string',
            'education_levels' => 'required|array',
            'education_levels.*' => 'required|exists:education_levels,id',
            'universities' => 'required|array',
            'universities.*' => 'required|exists:universities,id',
            'language_sectors' => 'required|array',
            'language_sectors.*' => 'required|exists:language_sectors,id',
            'school' => 'required|exists:schools,id',
            'subjects' => 'required|array',
            'subjects.*' => 'required|exists:subjects,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $teacher = User::where('mobile_phone', request('phone'))->whereNotNull('mobile_phone_verified_at')->first();
            if (!$teacher) {
                return $this->sendError(__('message.nomre_yoxdur_ve_ya_testiq_olunmayib'));
            }
            if($teacher->user_type){
                return $this->sendError(__('message.artiq_qeydiyyatdan_kecmisiniz'));
            }
            $avatar = new FileHelper(request('avatar'));

            $teacher->fullname = request('fullname');
            $teacher->birth = request('birth');
            $teacher->gender = request('gender');
            $teacher->email = request('email');
            $teacher->address = request('address');
            $teacher->user_type = 'teacher';
            $teacher->avatar = $avatar->getName();
            $teacher->save();

            $teacher_user = new TeacherUser;
            $teacher_user->user_id = $teacher->id;
            $teacher_user->active = 0;
            $teacher_user->save();

            $teacher->subjects()->attach(request('subjects'));
            $teacher->education_levels()->attach(request('education_levels'));
            $teacher->universities()->attach(request('universities'));
            $teacher->language_sectors()->attach(request('language_sectors'));
            $teacher->schools()->attach(request('school'));

            $avatar->save('images/avatars');
            DB::commit();
            return $this->sendSuccess(['inserted_id'=>$teacher->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }
}

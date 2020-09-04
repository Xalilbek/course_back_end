<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Users\TeacherUser;
use App\User;
use Illuminate\Support\Facades\DB;

class AdminTeacherController extends Controller
{
     /**
     * @api {post} /admin/teacher/register Admin. Register teacher
     * @apiGroup Admin
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
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
     *  "status" : true,
     * "data":{
     *  "inserted_id":3
     * }
     * }
     */
    public function registerTeacherByAdmin()
    {
        $validator = validator(request()->all(), [
            'fullname' => 'required|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png|max:10000',
            'birth' => 'required|date_format:Y-m-d|before:today',
            'gender' => 'required|in:m,f',
            'phone' => 'required',
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
            $check = User::where('mobile_phone', request('phone'))->exists();
            if ($check) {
                return $this->sendError(__('message.bu_nomre_artiq_movcuddur'));
            }
            $avatar = new FileHelper(request('avatar'));

            $teacher = new User;
            $teacher->mobile_phone = request('phone');
            $teacher->mobile_phone_verified_at = date('Y-m-d H:i:s');
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
            $teacher_user->active = 1;
            $teacher_user->save();

            $teacher->subjects()->attach(request('subjects'));
            $teacher->education_levels()->attach(request('education_levels'));
            $teacher->universities()->attach(request('universities'));
            $teacher->language_sectors()->attach(request('language_sectors'));
            $teacher->schools()->attach(request('school'));

            $avatar->save('images/avatars');
            DB::commit();
            return $this->sendSuccess(['inserted_id' => $teacher->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError([], $ex->getMessage());
        }
    }
    
     /**
     * @api {post} /admin/teacher/profile/edit/:id Admin. Edit profile teacher
     * @apiGroup Admin
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     *
     * @apiParam {integer} id
     * @apiParam {String} fullname
     * @apiParam {File} avatar
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
     *  "status" : true,
     * "data":null
     * }
     */
    public function editProfileTeacher($id)
    {
        $validator = validator(request()->all(), [
            'fullname' => 'required|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png|max:10000',
            'birth' => 'required|date_format:Y-m-d|before:today',
            'gender' => 'required|in:m,f',
            'phone' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
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
            $check = User::where('mobile_phone', request('phone'))->where('id','!=',$id)->exists();
            if ($check) {
                return $this->sendError(__('message.bu_nomre_artiq_movcuddur'));
            }
            $avatar = new FileHelper(request('avatar'));

            $teacher = User::where('id', $id)->where('user_type','teacher')->firstOrFail();
            $teacher->mobile_phone = request('phone');
            $teacher->mobile_phone_verified_at = date('Y-m-d H:i:s');
            $teacher->fullname = request('fullname');
            $teacher->birth = request('birth');
            $teacher->gender = request('gender');
            $teacher->email = request('email');
            $teacher->address = request('address');
            $teacher->user_type = 'teacher';
            if($avatar->has()){
                @unlink(public_path('images/avatars/'.$teacher->avatar_name));
                $teacher->avatar = $avatar->getName();
            }
            $teacher->save();

            $teacher->subjects()->sync(request('subjects'));
            $teacher->education_levels()->sync(request('education_levels'));
            $teacher->universities()->sync(request('universities'));
            $teacher->language_sectors()->sync(request('language_sectors'));
            $teacher->schools()->sync(request('school'));

            $avatar->save('images/avatars');
            DB::commit();
            return $this->sendSuccess();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError([], $ex->getMessage());
        }
    }

    /**
     * @api {get} /admin/teacher/status Teachers Status
     * @apiGroup Admin
     * @apiParam {Enum} [active] (in 0,1) default all
     * @apiParam {String} [name] filter fullname
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiSuccessExample Success-Response:
     * {
     *      "status": true,
     *      "data": {
     *          "current_page": 1,
     *          "data": [
     *              {
     *                  "id": 2,
     *                  "fullname": "Ziya Ziyali",
     *                  "username": null,
     *                  "teacher": {
     *                      "id": 1,
     *                      "user_id": 2,
     *                      "active": 0
     *                  }
     *              },
     *              {
     *                  "id": 3,
     *                  "fullname": "Professor",
     *                  "username": null,
     *                  "teacher": {
     *                      "id": 2,
     *                      "user_id": 3,
     *                      "active": 0
     *                  }
     *              },
     *              {
     *                  "id": 4,
     *                  "fullname": "Alim",
     *                  "username": null,
     *                  "teacher": {
     *                      "id": 3,
     *                      "user_id": 4,
     *                      "active": 0
     *                  }
     *              },
     *              {
     *                  "id": 5,
     *                  "fullname": "Semedov Semed",
     *                  "username": null,
     *                  "teacher": {
     *                      "id": 4,
     *                      "user_id": 5,
     *                      "active": 0
     *                  }
     *              }
     *          ],
     *          "first_page_url": "https://api.egundelik.com/admin/teacher/status?page=1",
     *          "from": 1,
     *          "last_page": 1,
     *          "last_page_url": "https://api.egundelik.com/admin/teacher/status?page=1",
     *          "next_page_url": null,
     *          "path": "https://api.egundelik.com/admin/teacher/status",
     *          "per_page": 20,
     *          "prev_page_url": null,
     *          "to": 4,
     *          "total": 4
     *      }
     *  }
     */
    public function teachersStatus(){
        $validator = validator(request()->all(),[
            'active' => 'nullable|in:0,1',
            'name' => 'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        $teachers = User::with(['teacher'])->onlyTeachers()
        ->when(request('active') != null, function($q){
            $q->whereHas('teacher', function($q){
                $q->where('active',request('active'));
            });
        })
        ->when(request('name'), function($q){
            $q->where('fullname','like','%'.request('name').'%');
        });
        return $this->sendSuccess($teachers->paginate(20,['id','fullname','username']));
    }

    /**
     * @api {put} /admin/teacher/status/:id change teacher status
     * @apiGroup Admin
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique user id
     * @apiParam {Enum} active (in 0,1)
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function teacherStatusChange($id){
        $validator = validator(request()->all(),[
            'active' => 'required|in:0,1'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        $teacher_user = TeacherUser::where('user_id', $id)->firstOrFail();
        $teacher_user->active = request('active');
        $teacher_user->save();
        return $this->sendSuccess();
    }

    /**
     * @api {put} /admin/teacher/profile/:id Get teacher profile by id
     * @apiGroup Admin
     *  
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id unique user id
     * @apiParam {Enum} active (in 0,1)
     * @apiSuccessExample Success-Response:
     *{
     *  "status": true,
     *  "data": {
     *    "id": 9,
     *    "username": null,
     *    "fullname": "Muellimov Muellim",
     *    "birth": "1993-10-10",
     *    "mobile_phone": "994777777777",
     *    "email": "test4@test.com",
     *    "address": "yasamal",
     *    "gender": "m",
     *    "education_levels": [
     *      {
     *        "id": 3,
     *        "name": "Professor",
     *        "list_order": "0",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "pivot": {
     *          "user_id": 9,
     *          "education_level_id": 3
     *        }
     *      },
     *      {
     *        "id": 2,
     *        "name": "Magistr",
     *        "list_order": "0",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "pivot": {
     *          "user_id": 9,
     *          "education_level_id": 2
     *        }
     *      }
     *    ],
     *    "language_sectors": [
     *      {
     *        "id": 2,
     *        "name": "ru_sector",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "pivot": {
     *          "user_id": 9,
     *          "language_sector_id": 2
     *        }
     *      },
     *      {
     *        "id": 3,
     *        "name": "en_sector",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "pivot": {
     *          "user_id": 9,
     *          "language_sector_id": 3
     *        }
     *      }
     *    ],
     *    "schools": [
     *      {
     *        "id": 3,
     *        "name": "239",
     *        "region_id": 1,
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "pivot": {
     *          "user_id": 9,
     *          "school_id": 3
     *        }
     *      }
     *    ],
     *    "universities": [
     *      {
     *        "id": 3,
     *        "name": "ADA",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "pivot": {
     *          "user_id": 9,
     *          "university_id": 3
     *        }
     *      },
     *      {
     *        "id": 2,
     *        "name": "ADNSU",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "pivot": {
     *          "user_id": 9,
     *          "university_id": 2
     *        }
     *      }
     *    ],
     *    "subjects": [
     *      {
     *        "id": 3,
     *        "name": "Azərbaycan dili",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "deleted_at": null,
     *        "pivot": {
     *          "user_id": 9,
     *          "subject_id": 3
     *        }
     *      },
     *      {
     *        "id": 2,
     *        "name": "Fizika",
     *        "created_at": "2020-05-11 17:24:50",
     *        "updated_at": "2020-05-11 17:24:50",
     *        "deleted_at": null,
     *        "pivot": {
     *          "user_id": 9,
     *          "subject_id": 2
     *        }
     *      }
     *    ]
     *  }
     *}
     */
    public function getTeacherById($id)
    {
        $user = User::with(['education_levels','language_sectors','schools','universities','subjects'])
            ->findOrFail($id,['id','username','fullname','birth','mobile_phone','email','address','gender']);

        return $this->sendSuccess($user);
    }
}

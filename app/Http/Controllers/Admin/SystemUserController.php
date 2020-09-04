<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\FileHelper;
use App\User;
use Illuminate\Support\Facades\DB;

class SystemUserController extends Controller
{
    /**
    * @api {get} /admin/system_user Get system users
    * @apiGroup Admin
    * 
    * @apiHeaderExample {json} Header-Example:
    * {
    *    "Accept":"application/json",
    *    "Content-lang":"az"
    * }
    *
    * @apiParam {String} name filter name
    * 
    * @apiSuccessExample Success-Response:
    *{
    *  "status": true,
    *  "data": {
    *    "current_page": 1,
    *    "data": [
    *      {
    *        "id": 17,
    *        "username": null,
    *        "fullname": "Test test",
    *        "avatar": null,
    *        "mobile_phone": "9942323412555",
    *        "email": "nicat@test.com",
    *        "birth": "2020-05-10",
    *        "address": "asdsd"
    *      }
    *    ],
    *    "first_page_url": "http://localhost:8000/admin/system_user?page=1",
    *    "from": 1,
    *    "last_page": 1,
    *    "last_page_url": "http://localhost:8000/admin/system_user?page=1",
    *    "next_page_url": null,
    *    "path": "http://localhost:8000/admin/system_user",
    *    "per_page": 10,
    *    "prev_page_url": null,
    *    "to": 1,
    *    "total": 1
    *  }
    *}
    */
    public function systemUsers()
    {
        $validator = validator(request()->all(),[
            'name' => 'nullable|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->toArray());
        }
        $users = User::where('user_type','user')
            ->where('is_superadmin',0)
            ->when(request('name'), function($q){
                $q->where('fullname','like','%'.request('name').'%');
            })
            ->paginate(10,[
                'id','username','fullname','avatar',
                'mobile_phone','email','birth', 'address'
            ]);

        return $this->sendSuccess($users);
    }
    
    /**
     * @api {post} /admin/system_user/register Register system user
     * @apiGroup Admin
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     *
     * @apiParam {String} username
     * @apiParam {String} password
     * @apiParam {String} fullname
     * @apiParam {File} [avatar]
     * @apiParam {Date} [birth] (format Y-m-d)
     * @apiParam {Enum} [gender] (in m,f)
     * @apiParam {Number} [phone]
     * @apiParam {String} email
     * @apiParam {String} [address]
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":{
     *  "inserted_id":3
     * }
     * }
     */
    public function registerSystemUser()
    {
        $validator = validator(request()->all(), [
            'username' => 'required|string|min:3|unique:users,username',
            'password' => 'required|string|min:6',
            'fullname' => 'required|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png|max:10000',
            'birth' => 'nullable|date_format:Y-m-d|before:today',
            'gender' => 'nullable|in:m,f',
            'phone' => 'nullable',
            'email' => 'nullable|email|unique:users,email',
            'address' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $avatar = new FileHelper(request('avatar'));

            $user = new User;
            $user->username = request('username');
            $user->password = bcrypt(request('password')) ;
            $user->mobile_phone = request('phone');
            $user->mobile_phone_verified_at = date('Y-m-d H:i:s');
            $user->fullname = request('fullname');
            $user->birth = request('birth');
            $user->gender = request('gender');
            $user->email = request('email');
            $user->address = request('address');
            $user->user_type = 'user';
            $user->avatar = $avatar->getName();
            $user->save();

            $avatar->save('images/avatars');
            DB::commit();
            return $this->sendSuccess(['inserted_id' => $user->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError([], $ex->getMessage());
        }
    }

    /**
     * @api {post} /admin/system_user/profile/edit/:id Edit profile system user
     * @apiGroup Admin
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     *
     * @apiParam {Integer} id
     * @apiParam {String} username
     * @apiParam {String} [password]
     * @apiParam {String} fullname
     * @apiParam {File} [avatar]
     * @apiParam {Date} [birth] (format Y-m-d)
     * @apiParam {Enum} [gender] (in m,f)
     * @apiParam {Number} [phone]
     * @apiParam {String} email
     * @apiParam {String} [address]
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function editProfileSystemUser($id)
    {
        $validator = validator(request()->all(), [
            'username' => 'required|string|min:3|unique:users,username,'.$id,
            'password' => 'nullable|string|min:6',
            'fullname' => 'required|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png|max:10000',
            'birth' => 'nullable|date_format:Y-m-d|before:today',
            'gender' => 'nullable|in:m,f',
            'phone' => 'nullable',
            'email' => 'nullable|email|unique:users,email,'.$id,
            'address' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $avatar = new FileHelper(request('avatar'));

            $user = User::where('id',$id)->where('user_type','user')->firstOrFail();
            $user->username = request('username');
            if(request('password')){
                $user->password = bcrypt(request('password')) ;
            }
            $user->mobile_phone = request('phone');
            $user->fullname = request('fullname');
            $user->birth = request('birth');
            $user->gender = request('gender');
            $user->email = request('email');
            $user->address = request('address');
            $user->avatar = $avatar->getName();
            if($avatar->has()){
                @unlink(public_path('images/avatars/'.$user->avatar_name));
                $user->avatar = $avatar->getName();
            }
            $user->save();

            $avatar->save('images/avatars');
            DB::commit();
            return $this->sendSuccess();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError([], $ex->getMessage());
        }
    }

    /**
     * @api {delete} /admin/system_user/:id Delete system user
     * @apiGroup Admin
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     *
     * @apiParam {Integer} id
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function deleteSystemUser($id)
    {
        $user = User::where('id', $id)->where('user_type', 'user')->where('is_superadmin',0)->firstOrFail();
        $user->delete();
        return $this->sendSuccess();
    }

    /**
     * @api {get} /admin/system_user/profile/:id Get system user profile by id
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
     * @apiSuccessExample Success-Response:
     *{
     *  "status": true,
     *  "data": {
     *    "id": 9,
     *    "username": "user1",
     *    "fullname": "User1",
     *    "birth": "1993-10-10",
     *    "mobile_phone": "994777777777",
     *    "email": "test4@test.com",
     *    "address": "yasamal",
     *    "gender": "m"
     *  }
     *}
     */
    public function getSystemUserById($id)
    {
        $user = User::findOrFail($id,['id','username','fullname','birth','mobile_phone','email','address','gender']);

        return $this->sendSuccess($user);
    }
}

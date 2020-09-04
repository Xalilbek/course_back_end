<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\SendSmsHelper;
use App\Http\Controllers\Controller;
use App\Models\Users\ParentUser;
use App\Models\Users\TeacherUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @api {post} /auth/login Login User
     * @apiGroup Auth
     * @apiName Login
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Number} phone
     * @apiSuccessExample Success-Response:
     * {
     * "status": true,
     * "data":{
     *      "message": "Verification code was sent to phone"
     *     }
     * }
     *
     * @apiErrorExample {json} Error-Response:
     * {
     * "status": false,
     * "errors": {
     * "phone": [
     * "The phone field is required."
     * ]
     * }
     * }
     */
    public function login()
    {
        $validator = validator(request()->all(), [
            'phone' => 'required|min:12|numeric'
        ]);

        if (strlen(request()->get('phone')) != 12) {
            return $this->sendError(__('message.nomre_sehvdir'));
        }

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        } else {
            $user = User::where('mobile_phone', request()->get('phone'))->first();

            if (is_null($user)) {
                $user = new User();
                $user->mobile_phone = request()->get('phone');
            }

            $code = rand(1000,9999);
            $check_sms = SendSmsHelper::send(request()->get('phone'),$code);
            if($check_sms->head->responsecode != "000"){
                return $this->sendSuccess(['message' => __('message.xeta_bizimle_elaqe_saxlayin')]);
            }
            $user->mobile_confirm_code = $code;
            $user->sms_send_date = now();
            $user->save();
            return $this->sendSuccess(['message' => __('message.testiq_kodu_gonderildi')]);
        }
    }

    /**
     * @api {post} /auth/confirm/phone Confirm Phone
     * @apiGroup Auth
     * @apiName Confirm Phone
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     * "Content-lang":"az"
     * }
     * 
     *
     * @apiParam {Number} phone
     * @apiParam {Number} code
     * @apiSuccessExample Success-Response:
     *{
     *  "status": true,
     *  "data": {
     *    "user_id": 3,
     *    "user_type": "student",
     *    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiN2MzNWUzMjdiOGYxYTI1MDI3YWI3MjI3ZDNkOTc3N2VlODVhYTJkMGI3ZGE2ODZmNmRlYTkwMjJmNmVkZDA4ZDRjNDc5NzVmZGRmYjRkYWEiLCJpYXQiOjE1ODkxMTUzOTksIm5iZiI6MTU4OTExNTM5OSwiZXhwIjoxNjIwNjUxMzk5LCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.NPm60EimQk0hs5S9q80ZqYJKZPaafIntt6N-0Vr0CqU8--pn6OmRt4VUa66jSv2H9Rq8jjs4TKW4cukbLPALrzIIUlG-5JEhbd8145fkB1ldjdMDOiM7QULtC9vqM4KTbNE2eHOjqYKDMJDtDx66m2Cic6y40kJO_qVQ_CwduwxiDlUNarDptRcReGXSDJS_xEpAoL0Y9-hwowLiIrb38oOBbPFy9rOibWRvr1lZ2ghPPZ9H58geQMJTsWb5aZZEm3a_O8kiwFPdIJ3bv4ugg93D2BD82XYXNBEeObjW_lziIKNdKwCAznkP8i3xokGfPFTgrCYYGuGJmSDDwS-8qxvJQzh1LhQq-FpOXDrqPxoyl0_EK2tP756zM69lGDGoRRALh79qH6NkGOi-C_lu9H6fKtcbyag3JSB0GkCO6DNs2cRagx_ZXJLv0UGCVDIqLyBQUQHRVqlgMcQcQkpoHlf2VBe4QlPgqO0DdXoRRbNi9WfSf9lfM0DpfATcaQfGY0HQ4jEC9Uqd5iho8mV7dTXLqazS-YhwaVfslIvBxir66uHJEdHYJ3xAdmHzLUlCsIUWDuZJmTBCU4ZvL5-pKXzkdliFML3Lfw8cHLPt46x2wW0K4Lol-M94gZR5tLuYojJpCPDt3WaVdMVjqn-M8vW2TvSbDTA9szJ_l-iXzT8"
     *  }
     *}
     *
     * @apiErrorExample {json} Error-Response:
     * {
     * "status": false,
     * "message": "User not found with this phone"
     * }
     */
    public function confirmPhone()
    {
        $validator = validator(request()->all(), [
            'phone' => 'required|min:10|numeric',
            'code' => 'required|min:4|numeric'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        } else {
            $user = User::where('mobile_phone', request()->get('phone'))
                ->where('mobile_confirm_code', request()->get('code'))
                ->whereNotNull('sms_send_date')->first();

            if (is_null($user)) {
                return $this->sendError(__('message.bu_nomre_movcud_deyil'));
            } else {
                if(Carbon::parse($user->sms_send_date) < now()->subSeconds(30)){
                    return $this->sendError(__('message.artiq_aktiv_deyil'));
                }
                $user->mobile_phone_verified_at = Carbon::now();
                $user->mobile_confirm_code = null;
                $user->sms_send_date = null;
                $user->save();
            }

            $tokenObj = $user->createToken('Egundelik API TOKEN');
            return $this->sendSuccess([
                'user_id'=>$user->id,
                'user_type' => $user->user_type,
                'token' => $tokenObj->accessToken,
            ]);
        }
    }

    /**
     * @api {post} /auth/change_type Change type parent and teacher
     * @apiGroup Auth
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
     *  "status": true,
     *  "data": {
     *    "now_type": "teacher"
     *  }
     *}
     */
    public function changeType()
    {
        $user = auth()->user();
        $type = $user->user_type;
        $check = false;
        if($type == 'parent'){
            $check = TeacherUser::where('user_id', auth()->id())->exists();
            if($check) $user->user_type = 'teacher';
        }
        else if($type == 'teacher'){
            $check = ParentUser::where('parent_id', auth()->id())->exists();
            if($check) $user->user_type = 'parent';
        }else{
            return $this->sendError('User type teacher ve ya parent olmalidi');
        }
        if(!$check){
            return $this->sendError('Tipi deyismek mumkun olmadi');
        }
        $user->save();
        return $this->sendSuccess(['now_type'=>$user->user_type]);
    }

    /**
     * @api {get} /auth/me User info by token
     * @apiGroup Auth
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiSuccessExample {json} Error-Response:
     *{
     *  "status": true,
     *  "data": {
     *    "address": "yasamal",
     *    "avatar": "https://api.egundelik.com/images/avatars/1588883701_downloadfiles_wallpapers_1366_768_ford_shelby_gt500_car_10107Eip3yGLfzR.jpg",
     *    "birth": "1993-10-10",
     *    "email": "test3@test.com",
     *    "fullname": "Məmmədov Məmməd",
     *    "gender": "m",
     *    "mobile_phone": "994772209923",
     *    "username": null
     *  }
     *}
     */
    public function me()
    {
        if(auth()->check()){
            return $this->sendSuccess(auth()->user()
                ->only([
                    'address', 'avatar', 'birth', 'email',
                    'fullname', 'gender', 'mobile_phone',
                    'username'
                ]));
        }
        return $this->sendError();
    }
}

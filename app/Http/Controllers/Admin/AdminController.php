<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\User;

class AdminController extends Controller
{     
    /**
    * @api {post} /admin/login Admin login
    * @apiGroup Admin
    * 
    * @apiHeaderExample {json} Header-Example:
    * {
    *    "Accept":"application/json",
    *    "Content-lang":"az"
    * }
    *
    * @apiParam {String} username
    * @apiParam {String} password
    * 
    * @apiSuccessExample Success-Response:
     *{
     *  "status": true,
     *  "data": {
     *    "user": {
     *      "id": 1,
     *      "username": "admin",
     *      "fullname": "Admin",
     *      "avatar": null,
     *      "birth": null,
     *      "mobile_phone": null,
     *      "gender": "m",
     *      "mobile_confirm_code": null,
     *      "mobile_phone_verified_at": null,
     *      "email": null,
     *      "email_verified_at": null,
     *      "user_type": "user",
     *      "address": null,
     *      "created_at": "2020-04-29 14:28:09",
     *      "updated_at": "2020-04-29 14:28:09",
     *      "deleted_at": null
     *    },
     *    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYjk5ZDdkMTNmODRhOTVlNTllNzRiMTEwNTU2YzZjZTM1MmQyYzMzOTU1YzNiN2IzMGI3N2EzZjU3ODU5OGNiZWE2ZmI0MmQyMDZlNWVjMTgiLCJpYXQiOjE1ODg2MzAzMDYsIm5iZiI6MTU4ODYzMDMwNiwiZXhwIjoxNjIwMTY2MzA2LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.Nb3kCHVOnYzSvhehI4v5UIA_uQ_rAUy9HN6Ce8csdEEgO_QfcLtJJUFaWcNXt1ndaRm5fa7vm_P2FU3dZBPGhon-p5i5lvKrPZp09fwl2uEpDnKxGPIrt2vWAY4N19_8USw015G6b-vV0tPH7zmEUYy83d13dTJ4n_mFeTHeDMhzn70ndSwZxQsh44ayfvKyIF-wizeEn-cAJwUGqvNWzAMx3YoHXzE_OeXgUrnyXsip4pnRCIvt5dia1OVF2bsdT6V7fD9Vzuau0_0m5EtR99XR7UBEfAjvEW6LNIUmI7rkUAW-JCNpVVpJlaYtG42ggobqNekghBr92Vx7o7A6Zowis3H4ZgB2jdvv4T95ZNkqBvHerH-jjiMtE25mFTIXBGza1Xokt4AJfv9lnNmixToda32un9s9sPuIvuIBDNmFzoZQbCh4IU3zdBY-APFMclAbimiqixf7Cluf6qM23XTji1NT5Liy1flLqr4BH6_lWeA0XY0R8QJZ-WSb-8OQxQZq28ybCiF_kcQzo5RkCIY6TND6p1t_Ald1ZvL80QMCSh5cv__s3hCWv3nTMcnUxoeYE5pcxiwXtsNg-ynDwKbm8tEQGn_DrI2jleS_6A4UP-QsbN3oc-LssTKhJ_IYLRViIDG7g0Td6ihtz-EH3AHHdW_fpr7VtJPqdwyZREA"
     *  }
     *}
    */
    public function loginAdmin()
    {
        $validator = validator(request()->all(),[
            'username' => 'required|string|min:3',
            'password' => 'required|string|min:6'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        if(auth()->attempt(['username'=>request('username'), 'password'=>request('password')])){
            $user = auth()->user();
            if($user->user_type == 'user'){
                $tokenObj = $user->createToken('Egundelik API TOKEN');
                return $this->sendSuccess([
                    'user' => $user,
                    'token' => $tokenObj->accessToken]);
            }
        }
        return $this->sendError();
    }
   
    /**
     * @api {delete} /admin/user/:id Delete user
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
    public function deleteUser($id)
    {
        $user = User::where('id', $id)->where('is_superadmin',0)->firstOrFail();
        $user->delete();
        return $this->sendSuccess();
    }

    /**
     * @api {post} /admin/setting Add or edit setting
     * @apiGroup Admin
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     *
     * @apiParam {Array} data
     * @apiParam {Enum} data.key (in:about,contact_address,contact_phone,help_description,help_title)
     * @apiParam {String} data.value 
     * 
     * @apiParamExample {json} Request-Example:
     *  {
     *      "data":[
     *          {
     *              "key":"about",
     *              "value":"haqqimizda test"
     *          },
     *          {
     *              "key":"contact_address",
     *              "value":"Bizimle elaqe"
     *          },
     *          {
     *              "key":"help_description",
     *              "value":"yardim test"
     *          }
     *      ]
     *  }
     * 
     * @apiSuccessExample Success-Response:
     * {
     *  "status" : true,
     * "data":null
     * }
     */
    public function addOrEditSettings()
    {
        $validator = validator(request()->all(),[
            'data' => 'required|array',
            'data.*.key' => 'required|string|in:about,contact_address,contact_phone,help_description,help_title',
            'data.*.value' => 'required|string'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        foreach(request('data') as $data){
            $setting = Setting::where('key', $data['key'])->first();
            if(!$setting){
                $setting = new Setting;
                $setting->key = $data['key'];
            }
            $setting->value = $data['value'];
            $setting->save();
        }
        return $this->sendSuccess();
    }
}

<?php

namespace App\Http\Controllers\Users;

use App\Helpers\SendSmsHelper;
use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller
{

    /**
     * @api {post} /user/getuserbynumber Get User
     * @apiGroup User
     * @apiName Get
     *
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json"
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
    public function getUserByNumber(){
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


            return $this->sendSuccess(['inserted_id'=> $user->id,'mobile_phone'=>$user->mobile_phone]);
        }
    }
}

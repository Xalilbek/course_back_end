<?php

namespace App\Http\Controllers\Users;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Users\StudentUser;
use App\User;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * @api {post} /student/register Register student
     * @apiGroup Student
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
     * @apiParam {Integer} school unique school_id
     * @apiParam {Integer} class_number 
     * @apiParam {Integer} language_sector 
     * @apiParam {Integer} group 
     * @apiParam {Integer} relation unique relation id 
     * @apiParam {Sting} parent_fullname
     * @apiParam {Number} parent_phone
     * @apiParam {Date} parent_birth (format Y-m-d)
     * @apiSuccessExample Success-Response:
     *      {
     *      "status": true,
     *      "data": {
     *          "inserted_id": 13,
     *          "parent_id": 36
     *      }
     *  }
     */
    public function registerStudent()
    {
        $validator = validator(request()->all(), [
            'fullname' => 'required|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png|max:10000',
            'birth' => 'required|date_format:Y-m-d|before:today',
            'gender' => 'required|in:m,f',
            'school' => 'required|integer|exists:schools,id',
            'class_number' => 'required|integer|min:1|max:11',
            'language_sector' => 'required|integer|exists:language_sectors,id',
            'group' => 'required|integer|min:1|max:4',
            'relation' => 'required|integer|exists:relations,id',
            'phone' => 'required|numeric|min:10',
            'parent_fullname' => 'required|string',
            'parent_phone' => 'required|numeric|min:10',
            'parent_birth' => 'required|date_format:Y-m-d|before:today'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        DB::beginTransaction();
        try {
            $student = User::where('mobile_phone', request('phone'))->whereNotNull('mobile_phone_verified_at')->first();
            if (!$student) {
                return $this->sendError(__('message.nomre_yoxdur_ve_ya_testiq_olunmayib'));
            }
            if($student->user_type){
                return $this->sendError(__('message.artiq_qeydiyyatdan_kecmisiniz'));
            }
            $avatar = new FileHelper(request('avatar'));

            $student->fullname = request('fullname');
            $student->birth = request('birth');
            $student->gender = request('gender');
            $student->user_type = 'student';
            $student->avatar = $avatar->getName();
            $student->save();

            $parent = User::where('mobile_phone', request('parent_phone'))->first();
            if(!$parent){
                $parent = new User;
                $parent->mobile_phone = request('parent_phone');
                $parent->fullname = request('parent_fullname');
                $parent->birth = request('parent_birth');
                $parent->user_type = 'parent';
                $parent->save();
            }

            $student_user = new StudentUser;
            $student_user->user_id = $student->id;
            $student_user->class_number = request('class_number');
            $student_user->university_group = request('group');
            $student_user->save();

            $student->parents()->attach([$parent->id => ['relation_id' => request('relation')]]);
            $student->schools()->attach(request('school'));
            $student->language_sectors()->attach(request('language_sector'));

            $avatar->save('images/avatars');

            DB::commit();
            return $this->sendSuccess(['inserted_id'=> $student->id,'parent_id'=>$parent->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }
}

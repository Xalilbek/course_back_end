<?php

namespace App\Http\Controllers\Lesson;

use App\Helpers\Lesson\AnonsHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson\Anons;
use App\Models\Lesson\LessonGroup;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class AnonsController extends Controller
{
    public function __construct()
    {
        $this->middleware('user_type:teacher');
    }

    /**
     * @api {post} /lesson/anons create anons
     * @apiGroup Teacher
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     * "Content-lang":"az"
     * }
     * 
     * @apiParam {Object} lesson_groups unique lesson_groups id
     * @apiParam {String} text
     * @apiParam {Enum} type (in student,teachers,all) default = all
     * 
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": {
     *          "users": [
     *              {
     *                  "id": 29,
     *                  "fullname": "Quliyev Ehmed",
     *                  "avatar": "https://api.egundelik.com/images/avatars/~~~4MW9PiTl9O1584892726.jpg"
     *              },
     *              {
     *                  "id": 34,
     *                  "avatar": null,
     *                  "fullname": "Quliyev Vasif"
     *              }
     *          ],
     *          "text": "test all",
     *          "created_at": "29-03-2020 12:56:39"
     *      }
     *  }
     */
    public function addAnons()
    {
        $validator = validator(request()->all(), [
            'lesson_groups' => 'required|array',
            'lesson_groups.*' => 'required|integer',
            'text' => 'required|string',
            'type' => 'nullable|in:parents,students,all'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $lesson_groups = LessonGroup::with([
            'lesson_group_students' => function ($q) {
                $q->select(['id', 'lesson_group_id', 'student_id'])
                    ->where('status', 'accept')
                    ->with('student:id,fullname,avatar');
            }
        ])->findOrFail(request('lesson_groups'), ['id', 'teacher_id']);

        foreach ($lesson_groups as $group) {
            $this->authorize('check_id', $group->teacher_id);
        }
        $type = Anons::getType(request('type'));
        $students_and_parents = AnonsHelper::getGroupStudentsAndParents($lesson_groups, $type);
        $all_users = $students_and_parents['all_users'];
        $all_students_id = $students_and_parents['all_students_id'];
        $all_parents_id = $students_and_parents['all_parents_id'];
        $all_ids = array_merge($all_students_id, $all_parents_id);

        if (count($all_ids) == 0) {
            return $this->sendError(__('message.anons_heckim_tapilmadi'));
        }
        DB::beginTransaction();
        try {
            $anons = new Anons;
            $anons->user_id = auth()->id();
            $anons->send_type = $type;
            $anons->text = request('text');
            $anons->student_count = count($all_students_id);
            $anons->parent_count = count($all_parents_id);
            $anons->save();
            $anons->lesson_groups()->attach($lesson_groups->pluck('id'));
            Notification::sendAnons($all_ids, $anons);
            $created_at = $anons->created_at->format('d-m-Y H:i:s');
            DB::commit();
            return $this->sendSuccess([
                'users' => $all_users,
                'text' => $anons->text,
                'created_at' => $created_at
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * @api {get} /lesson/anons/count anons count students and parents
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
     *  {
     *      "status": true,
     *      "data": {
     *          "student_count": 4,
     *          "parent_count": 2
     *      }
     *  }
     */
    public function getAnonsCount()
    {
        $anons = Anons::where('user_id', auth()->id())->get(['student_count', 'parent_count']);
        $student_count = $anons->sum('student_count');
        $parent_count = $anons->sum('parent_count');
        return $this->sendSuccess(['student_count' => $student_count, 'parent_count' => $parent_count]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * @api {get} /notification Notifications
     * @apiGroup Notification
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     *
     * @apiParam {Enum} [type] (in seen, not_seen) default all
     * @apiSuccessExample Success-Response:
     *      {
     *      "status": true,
     *      "data": {
     *          "current_page": 1,
     *          "data": [
     *              {
     *                  "id": 5,
     *                  "title": "Qrupa qosulma isteyi",
     *                  "content": "Sagirdov sagird sizin Qrup 1 qrupunda 2 gun saat 10:00:00 olan derse qosulmaq isteyir",
     *                  "user_id": 6,
     *                  "sender_id": 8,
     *                  "type": "request_group",
     *                  "seen": 1,
     *                  "related_id":2,
     *                  "created_at": "2020-03-08 13:15:38",
     *                  "updated_at": "2020-03-08 13:19:25"
     *              },
     *              {
     *                  "id": 6,
     *                  "title": "Qrupa qosulma isteyi",
     *                  "content": "Sagirdov sagird sizin Qrup 1 qrupunda 4 gun saat 12:00:00 olan derse qosulmaq isteyir",
     *                  "user_id": 6,
     *                  "sender_id": 8,
     *                  "type": "request_group",
     *                  "seen": 0,
     *                  "related_id":3,
     *                  "created_at": "2020-03-08 13:16:22",
     *                  "updated_at": "2020-03-08 13:16:22"
     *              }
     *          ],
     *          "first_page_url": "https://api.egundelik.com/notification?page=1",
     *          "from": 1,
     *          "last_page": 1,
     *          "last_page_url": "https://api.egundelik.com/notification?page=1",
     *          "next_page_url": null,
     *          "path": "https://api.egundelik.com/notification",
     *          "per_page": 20,
     *          "prev_page_url": null,
     *          "to": 2,
     *          "total": 2
     *      }
     *  }
     */
    public function allNotificationsByAuthId()
    {
        $validator = validator(request()->all(), [
            'type' => 'nullable|in:seen, not_seen'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }
        if (request('type')) {
            $type = request('type') == 'seen' ? 1 : 0;
            return $this->sendSuccess(Notification::user()->where('seen', $type)->paginate(20));
        } else {
            return $this->sendSuccess(Notification::user()->paginate(20));
        }
    }
    /**
     * @api {get} /notification/:id Notification By Id
     * @apiGroup Notification
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     * @apiParam {Integer} id
     * @apiSuccessExample Success-Response:
     *      {
     *      "status": true,
     *      "data": {
     *          "id": 5,
     *          "title": "Qrupa qosulma isteyi",
     *          "content": "Sagirdov sagird sizin Qrup 1 qrupunda 2 gun saat 10:00:00 olan derse qosulmaq isteyir",
     *          "user_id": 6,
     *          "sender_id": 8,
     *          "type": "request_group",
     *          "seen": 1,
     *          "related_id":2,
     *          "created_at": "2020-03-08 13:15:38",
     *          "updated_at": "2020-03-08 13:19:25"
     *      }
     *  }
     */
    public function getNotificationById($id)
    {
        $notification = Notification::findOrFail($id);
        $this->authorize('check_id',$notification->user_id);
        $notification->seen = 1;
        $notification->save();
        return $this->sendSuccess($notification);
    }
}

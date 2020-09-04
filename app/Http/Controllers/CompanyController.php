<?php

namespace App\Http\Controllers;

use App\Models\Company;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('user_type:user')->except(['getCompantList','showCompany']);
    }

    /**
     * @api {get} /company Get company list
     * @apiGroup Company
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     *
     * @apiParam {Integer} [category] unique company category id
     * 
     * @apiSuccessExample Success-Response:
     *{
     *  "status": true,
     *  "data": {
     *    "current_page": 1,
     *    "data": [
     *      {
     *        "id": 1,
     *        "user_type": "teacher",
     *        "company_category_id": 1,
     *        "title": "test",
     *        "description": "first",
     *        "image_url": "asdadasd",
     *        "longitude": "12.1543540",
     *        "latitude": "12.5126455"
     *      }
     *    ],
     *    "first_page_url": "api.egundelik.com/company?page=1",
     *    "from": 1,
     *    "last_page": 1,
     *    "last_page_url": "api.egundelik.com/company?page=1",
     *    "next_page_url": null,
     *    "path": "api.egundelik.com/company",
     *    "per_page": 10,
     *    "prev_page_url": null,
     *    "to": 1,
     *    "total": 1
     *  }
     *}
     */
    public function getCompanyList()
    {
        $validator = validator(request()->all(),[
            'category'=>'nullable|integer|exists:company_categories,id'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $type = auth()->user()->user_type;
        $companies = Company::when(request('category'), function($q){
                                $q->where('company_category_id',request('category'));
                            })
                            ->whereIn('user_type',[$type, 'all'])
                            ->paginate(10);
        return $this->sendSuccess($companies);
    }

    /**
     * @api {get} /company/:id Show company
     * @apiGroup Company
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     *
     * @apiParam {Integer} category unique company category id
     * @apiSuccessExample Success-Response:
     *{
     *  "status": true,
     *  "data": {
     *    "id": 1,
     *    "user_type": "all",
     *    "company_category_id": 1,
     *    "title": "ilk test",
     *    "description": "first",
     *    "image_url": "asdadasd",
     *    "longitude": "12.1543540",
     *    "latitude": "12.5126455",
     *    "created_at": "2020-05-02 17:36:19",
     *    "updated_at": "2020-05-02 17:36:19"
     *  }
     *}
     */
    public function showCompany($id)
    {
        $company = Company::findOrFail($id);
        return $this->sendSuccess($company);
    }

    /**
     * @api {post} /company Create company
     * @apiGroup Company
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     *
     * @apiParam {Enum} [user_type] (in teacher, student, parent, all) default all
     * @apiParam {Integer} category unique company category id
     * @apiParam {String} title
     * @apiParam {String} description
     * @apiParam {String} image_url
     * @apiParam {Decimal} longitude
     * @apiParam {Decimal} latitude
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": {
     *          "inserted_id": 1
     *          }
     *  }
     */
    public function createCompany()
    {
        $validator = validator(request()->all(),[
            'user_type'=>'nullable|in:teacher,student,parent,all',
            'category'=>'required|integer|exists:company_categories,id',
            'title' => 'required|string',
            'description'=>'required|string',
            'image_url'=>'required|string',
            'longitude'=>'required|numeric|max:999|min:-999',
            'latitude'=>'required|numeric|max:999|min:-999'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $company = new Company;
        $company->user_type = request('user_type') ?: 'all';
        $company->company_category_id = request('category');
        $company->title = request('title');
        $company->description = request('description');
        $company->image_url = request('image_url');
        $company->longitude = request('longitude');
        $company->latitude = request('latitude');
        $company->save();
        $company->createLog();
        return $this->sendSuccess(['inserted_id'=>$company->id]);
    }

    /**
     * @api {put} /company/:id Update company
     * @apiGroup Company
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     *
     * @apiParam {Integer} id unique company id
     * @apiParam {Enum} [user_type] (in teacher, student, parent, all) default all
     * @apiParam {Integer} category unique company category id
     * @apiParam {String} title
     * @apiParam {String} description
     * @apiParam {String} image_url
     * @apiParam {Decimal} longitude
     * @apiParam {Decimal} latitude
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function updateCompany($id)
    {
        $validator = validator(request()->all(),[
            'user_type'=>'nullable|in:teacher,student,parent,all',
            'category'=>'required|integer|exists:company_categories,id',
            'title' => 'required|string',
            'description'=>'required|string',
            'image_url'=>'required|string',
            'longitude'=>'required|numeric|max:999|min:-999',
            'latitude'=>'required|numeric|max:999|min:-999'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $company = Company::findOrFail($id);
        $company->user_type = request('user_type') ?: 'all';
        $company->company_category_id = request('category');
        $company->title = request('title');
        $company->description = request('description');
        $company->image_url = request('image_url');
        $company->longitude = request('longitude');
        $company->latitude = request('latitude');
        $company->save();
        $company->createLog('updated');
        return $this->sendSuccess();
    }

    /**
     * @api {delete} /company/:id Delete company
     * @apiGroup Company
     * 
     * @apiHeaderExample {json} Header-Example:
     * {
     *    "Accept":"application/json",
     *    "Authorization":"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODg3MzBmODc3YWM5ODZmMDQ0OGIyYmZjZTEwMDQxNzk4Njc4MGUxZDE5MTUwNGMxMjdiODgzNjQ4YjczY2QxZTAyNjQxZjlhYjQzOTE1ZTQiLCJpYXQiOjE1ODQ4OTk0OTQsIm5iZiI6MTU4NDg5OTQ5NCwiZXhwIjoxNjE2NDM1NDk0LCJzdWIiOiIyOSIsInNjb3BlcyI6W119.BP7BYGWBzG_o7GEkTvwMWOIc3OPGnzXMFfqcTPSIFDAoEyy5omsxHfSGjZ5xD1VNLD90hEQph3_G_VWXFDohgsq3UIyBA-rLcXwCQN_ZMCthZRA9jxlj8ZpNgb60-cSLjuFTeeFWsYOaszlxSa1nuAu_9mMEvcByv10XXiC5p-clgpSH_e23Fparh2VJrHoeKJrlqGwHSLR6Z-Gjf4OgeCvKpS4v4MEWFz1_Y_XgbZSRH2YCYWzCwBRn9SHzDSpjZ-QmA2o0lsszn9LaqQ-jC_pUpA031mNkDyLliD08mHcouyijUlB0_hmK2dtxGrEmfDd2XKfKTSMzVM3rfv8h9qNtv-WcWJA_llXiv-d1spl1qk5QpShFUtqO8aqMOzI1s3CgZ8gnL6RKghSTOsB3KQJavsR76i9qExeh_GFp10PxbZeobdcIIsvbDiD43SghVejfJZDc07hshjIoOT6Yt6BUkqPTb8QyrmTHkboP1gF4rTyDR1dMwAyIX2-J8P2XnkPPDjhYrTist4nXBa0EON10KYuK8GJTR5rHQLqzyDYWveiM_dMLDQizcknY00ZCtLoYa0HzQ08UlyYRH6BD1aDzdmLinzqGNCgZgkDCEqHRQZPR8is5iHBc9D1xPiS2JKGXUJeXslh0NhWx3t8WFnbqJiZAmP0xe7KRqmkjlwo",
     *    "Content-lang":"az"
     * }
     * 
     *
     * @apiParam {Integer} id unique company id
     * 
     * @apiSuccessExample Success-Response:
     *  {
     *      "status": true,
     *      "data": null
     *  }
     */
    public function deleteCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->createLog('deleted');
        $company->delete();
        return $this->sendSuccess();
    }
}

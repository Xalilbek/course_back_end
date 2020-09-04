<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function sendSuccess($data = null)
    {
        return response()->json(['status' => true, 'data' => $data]);
    }
    public function sendError($errors = [], $message = null)
    {
        if (func_num_args() == 0) {
            $message = __('message.xeta_var');
        } else if (is_string($errors) && func_num_args() == 1) {
            $message = (string)$errors;
            $errors = [];
        }
        return response()->json(['status' => false, 'errors' => $errors, 'message' => $message]);
    }
}

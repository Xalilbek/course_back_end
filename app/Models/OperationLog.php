<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationLog extends Model
{
    public $timestamps = false;

    public function getDataAttribute($value)
    {
        return json_decode($value);
    }
}

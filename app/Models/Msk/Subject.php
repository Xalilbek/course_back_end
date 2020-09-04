<?php

namespace App\Models\Msk;

use App\Traits\OperationLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes, OperationLogTrait;
}

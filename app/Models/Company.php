<?php

namespace App\Models;

use App\Traits\OperationLogTrait;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use OperationLogTrait;
}

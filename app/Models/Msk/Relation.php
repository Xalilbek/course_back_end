<?php

namespace App\Models\Msk;

use App\Traits\OperationLogTrait;
use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    use OperationLogTrait;
    public $timestamps = false;
}

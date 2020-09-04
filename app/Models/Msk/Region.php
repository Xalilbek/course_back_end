<?php

namespace App\Models\Msk;

use App\Traits\OperationLogTrait;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use OperationLogTrait;
    public $timestamps = false;

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}

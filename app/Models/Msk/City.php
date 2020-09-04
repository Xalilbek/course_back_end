<?php

namespace App\Models\Msk;

use App\Traits\OperationLogTrait;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use OperationLogTrait;
    public $timestamps = false;

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}

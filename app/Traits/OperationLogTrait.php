<?php

namespace App\Traits;

use App\Models\OperationLog;

trait OperationLogTrait
{
    public function createLog(string $type = 'created')
    {
        $log = new OperationLog;
        $log->user_id = auth()->id();
        $log->table_name = $this->getTable();
        $log->related_id = $this->getKey();
        $log->type = $type;
        if ($type != 'created') {
            $log->data = json_encode($this->attributes);
        }
        $log->date = now();
        $log->save();
    }
}

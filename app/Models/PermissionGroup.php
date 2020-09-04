<?php

namespace App\Models;

use App\Traits\OperationLogTrait;
use App\User;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use OperationLogTrait;
    
    public function modules()
    {
        return $this->hasMany(PermissionGroupModule::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_group_users');
    }

    public function syncModule($modules)
    {
        $modules_name = [];
        foreach ($modules as $module) {
            $modules_name[] = $module['module_name'];
            $permission_module = PermissionGroupModule::where('module_name', $module['module_name'])
                ->where('permission_group_id', $this->id)->first();
            if(!$permission_module){
                $permission_module = new PermissionGroupModule;
                $permission_module->permission_group_id = $this->id;
                $permission_module->module_name = $module['module_name'];
            }
            $permission_module->permission_type = $module['permission_type'];
            $permission_module->save();
        }
        PermissionGroupModule::whereNotIn('module_name', $modules_name)
            ->where('permission_group_id', $this->id)
            ->delete();
    }
}

<?php

namespace App\Http\Middleware;

use Closure;

class PermissionFullMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $module_name)
    {
        $user = auth()->user();
        if($user->is_superadmin){
            return $next($request);
        }
        if ($user->user_type === 'user') {
            $user->load('permission_groups.modules');
            $modules = $user->permission_groups->pluck('modules')->toArray();
            $modules = isset($modules[0]) ? $modules[0] : [];
            foreach ($modules as $module) {
                if ($module['module_name'] === $module_name) {
                    if($module['permission_type'] === 'full'){
                        return $next($request);
                    }
                }
            }
        }
        return response()->json(['Icazeniz yoxdur [permission_full]']);
    }
}

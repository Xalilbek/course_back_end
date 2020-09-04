<?php

namespace App\Http\Middleware;

use Closure;

class UserTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $types = 'user')
    {
        $user = auth()->user();
        if($user->is_superadmin){
            return $next($request);
        }
        if($types === 'superadmin'){
            return response()->json(['Icazeniz yoxdur. [user_type_is_super_admin]']);
        }
        $types = explode(',',$types);
        $user_type = $user->user_type;
        foreach ($types as $type) {
            if($user_type === $type){
                if($type === 'teacher'){
                    if($this->isActiveTeacher($user)){
                        return $next($request);
                    }
                }else{
                    return $next($request);
                }
            }
        }
        return response()->json(['Icazeniz yoxdur. [user_type]']);
    }

    public function isActiveTeacher($user): bool
    {
        $teacher = $user->teacher;
        if(isset($teacher->active) && $teacher->active){
            return true;
        }
        return false;
    }
}

<?php



//    admin routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', function () {
        return response()->json(['status' => true, 'token' => 'sadadadadada']);
    });
});
Route::post('login', 'Admin\AdminController@loginAdmin');

Route::namespace('Admin')
    ->middleware([
        'auth:api',
        'user_type:superadmin'
    ])->group(function () {
        Route::delete('user/{id}', 'AdminController@deleteUser');
        Route::prefix('system_user')->group(function () {
            Route::get('profile/{id}', 'SystemUserController@getSystemUserById');
            Route::post('register', 'SystemUserController@registerSystemUser');
            Route::post('profile/edit/{id}', 'SystemUserController@editProfileSystemUser');
            Route::delete('{id}', 'SystemUserController@deleteSystemUser');
        });
        Route::prefix('permission')->group(function () {
            Route::post('group/module', 'PermissionController@addGroupModules');
            Route::post('group', 'PermissionController@addGroup');
            Route::put('group/{id}', 'PermissionController@editGroup');
            Route::delete('group/{id}', 'PermissionController@deleteGroup');
        });
    });

Route::namespace('Admin')
    ->middleware([
        'auth:api',
        'user_type:user'
    ])->group(function () {
        Route::post('setting', 'AdminController@addOrEditSettings');
        Route::prefix('teacher')->group(function () {
            Route::get('profile/{id}', 'AdminTeacherController@getTeacherById');
            Route::post('register', 'AdminTeacherController@registerTeacherByAdmin');
            Route::post('profile/edit/{id}', 'AdminTeacherController@editProfileTeacher');
            Route::get('status', 'AdminTeacherController@teachersStatus');
            Route::put('status/{id}', 'AdminTeacherController@teacherStatusChange');
        });
        Route::prefix('group')->group(function(){
            Route::post('','AdminGroupController@addLessonGroup');
            Route::post('decline/{id}','AdminGroupController@lessonGroupStudentDecline');
            Route::post('restore/{id}','AdminGroupController@restoreLessonGroup');
            Route::put('{id}','AdminGroupController@updateLessonGroup');
            Route::delete('{id}','AdminGroupController@deleteLessonGroup');
        });
        Route::prefix('system_user')->group(function () {
            Route::get('', 'SystemUserController@systemUsers');
        });
        Route::prefix('permission')->group(function () {
            Route::get('group', 'PermissionController@getGroups');
            Route::get('group/{id}', 'PermissionController@getGroupById');
        });
    });

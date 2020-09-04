<?php
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With, Application');

Route::prefix('auth')->namespace('Auth')->group(function () {
    Route::post('/login', 'AuthController@login');
    Route::post('/confirm/phone', 'AuthController@confirmPhone');
    Route::middleware('auth:api')->group(function(){
        Route::get('/me', 'AuthController@me');
        Route::post('/change_type', 'AuthController@changeType');
    });
});
Route::namespace('Users')->group(function () {
    Route::post('teacher/register', 'TeacherController@registerTeacher');
    Route::post('student/register', 'StudentController@registerStudent');
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('notification', 'NotificationController@allNotificationsByAuthId');
    Route::get('notification/{id}', 'NotificationController@getNotificationById');

    Route::prefix('company')->group(function () {
        Route::get('/', 'CompanyController@getCompanyList');
        Route::get('/{id}', 'CompanyController@showCompany');
        Route::post('/', 'CompanyController@createCompany');
        Route::put('{id}', 'CompanyController@updateCompany');
        Route::delete('{id}', 'CompanyController@deleteCompany');
    });

    Route::namespace('Users')->group(function () {
        Route::prefix('teacher')->group(function () {
            Route::get('','TeacherController@allTeachersAndFilter');
        });
    });

    Route::namespace('Lesson')->prefix('lesson')->group(function () {
        Route::get('lesson_day/excellent_student','LessonDayController@excellentStudent');
        Route::get('lesson_day/student','LessonDayController@showAttendanceStudent');
        Route::get('lesson_day/show/{id}','LessonDayController@showLessonDay');
        Route::get('lesson_day/parent_seen/log','LessonDayController@parentSeenLog');
        Route::get('lesson_day/home_work/log','LessonDayController@homeWorkLog');
        Route::get('lesson_day/lesson_work/log','LessonDayController@lessonWorkLog');
        Route::get('lesson_day/attendance/log','LessonDayController@attendanceLog');
        Route::post('lesson_day/excellent_student/greeting','LessonDayController@greetingExcellentStudent');

        Route::get('group/show/lesson/{id}', 'LessonGroupController@showGroupTeacherAndStudentsByLessonId');
        Route::get('group/show/lessons/lesson/{id}', 'LessonGroupController@showGroupLessonsByLessonId');
        Route::get('group/show/active_subjects', 'LessonGroupController@showActiveSubjects');
        Route::get('group/reyting/subject/{id}', 'LessonGroupController@showReytingBySubjectId');

        Route::middleware('user_type:teacher')->group(function(){
            Route::get('group/{id}','LessonGroupController@showLessonGroup');
            Route::post('group', 'LessonGroupController@addLessonGroup');
            Route::put('group/{id}', 'LessonGroupController@updateLessonGroup');
            Route::delete('group/{id}', 'LessonGroupController@deleteLessonGroup');

            Route::post('lesson','LessonController@createLesson');
            Route::put('lesson/{id}','LessonController@updateLesson');
            Route::delete('lesson/{id}','LessonController@deleteLesson');

            Route::post('operation/transfer','LessonOperationController@transferLesson');
            Route::post('operation/cancel','LessonOperationController@cancelLesson');
            Route::post('operation/add','LessonOperationController@addLesson');
            Route::delete('operation/{id}','LessonOperationController@deleteLessonOperation');
            
            Route::get('lesson_day/lesson/{id}','LessonDayController@getLessonDayByLessonId');
            Route::get('lesson_day/seen_attendance','LessonDayController@seenAttendanceCount');
            Route::get('lesson_day/seen_student_attendance','LessonDayController@seenStudentAttendanceCount');
            Route::get('lesson_day/attendance/statistic','LessonDayController@attendanceStatistic');
            Route::post('lesson_day','LessonDayController@addOrUpdateLessonDayByTeacher');
            Route::post('lesson_day/type','LessonDayController@addOrUpdateType');
            Route::post('lesson_day/home_work','LessonDayController@home_work');
            Route::post('lesson_day/lesson_work','LessonDayController@lesson_work');
            Route::delete('lesson_day/{id}','LessonDayController@deleteLessonDay');

            Route::post('anons','AnonsController@addAnons');
            Route::get('anons/count','AnonsController@getAnonsCount');
            Route::get('teacher/lessons_day_by_date','LessonController@getLessonsDayByDate');
            Route::post('teacher/accept','LessonGroupStudentController@studentAccept');
            Route::post('teacher/decline','LessonGroupStudentController@studentDecline');
            Route::post('teacher/transfer','LessonGroupStudentController@studentTransfer');
            Route::post('teacher/add','LessonGroupStudentController@studentAddNewGroup');
            Route::post('change_status', 'LessonGroupStudentController@changeStudentStatus');
            Route::get('teacher/search', 'LessonGroupController@searchParentsAndStudents');

            // by lesson id
            Route::get('lesson_day/seen_attendance_by_lesson','LessonDayController@seenAttendanceCountByLessonId');
            Route::get('lesson_day/seen_student_attendance_by_lesson','LessonDayController@seenStudentAttendanceCountByLessonId');
            
            Route::post('teacher/lesson/accept','LessonGroupStudentController@studentAcceptByLessonId');
            Route::post('teacher/lesson/decline','LessonGroupStudentController@studentDeclineByLessonId');
            Route::post('teacher/lesson/transfer','LessonGroupStudentController@studentTransferByLessonId');
            Route::post('teacher/lesson/add','LessonGroupStudentController@studentAddNewGroupByLessonId');

            Route::get('teacher/group/lesson/{id}','LessonGroupController@showLessonGroupByLessonId');
            Route::get('teacher/group','LessonGroupController@getTeacherGroupsAndLessons');
            Route::get('teacher/group/reyting','LessonGroupController@getTeacherReytingStudents');
            Route::put('teacher/group/lesson/{id}','LessonGroupController@updateLessonGroupByLessonId');
            Route::delete('teacher/group/lesson/{id}','LessonGroupController@deleteLessonGroupByLessonId');
        });

        Route::middleware('user_type:student')->group(function(){
            Route::post('student', 'LessonGroupStudentController@addLessonGroupStudent');
            Route::post('lesson_day/student/absent', 'LessonDayController@studentAbsent');
            Route::get('search_groups_by_week_day/{week_day}', 'LessonStudentController@searchGroupsByWeekDay');
            Route::get('student/search', 'LessonGroupController@searchTeachersAndStudents');
        });
    });

    Route::prefix('msk')->namespace('Msk')->group(function () {
        Route::prefix('relation')->group(function () {
            Route::get('/', 'RelationController@index');
            Route::post('/', 'RelationController@store');
            Route::delete('/{id}', 'RelationController@destroy');
            Route::put('/{id}', 'RelationController@update');
        });
        Route::prefix('school')->group(function () {
            Route::get('/', 'SchoolController@index');
            Route::post('/', 'SchoolController@store');
            Route::delete('/{id}', 'SchoolController@destroy');
            Route::put('/{id}', 'SchoolController@update');
        });
        Route::prefix('subject')->group(function () {
            Route::get('/', 'SubjectController@index');
            Route::post('/', 'SubjectController@store');
            Route::delete('/{id}', 'SubjectController@destroy');
            Route::put('/{id}', 'SubjectController@update');
        });
        Route::prefix('city')->group(function () {
            Route::get('/', 'CityController@index');
            Route::post('/', 'CityController@store');
            Route::delete('/{id}', 'CityController@destroy');
            Route::put('/{id}', 'CityController@update');
        });
        Route::prefix('region')->group(function () {
            Route::get('/', 'RegionController@index');
            Route::post('/', 'RegionController@store');
            Route::delete('/{id}', 'RegionController@destroy');
            Route::put('/{id}', 'RegionController@update');
        });
        Route::prefix('education_level')->group(function () {
            Route::get('/', 'EducationLevelController@index');
            Route::post('/', 'EducationLevelController@store');
            Route::delete('/{id}', 'EducationLevelController@destroy');
            Route::put('/{id}', 'EducationLevelController@update');
        });
        Route::prefix('university')->group(function () {
            Route::get('/', 'UniversityController@index');
            Route::post('/', 'UniversityController@store');
            Route::delete('/{id}', 'UniversityController@destroy');
            Route::put('/{id}', 'UniversityController@update');
        });
        Route::prefix('company_category')->group(function () {
            Route::get('/', 'CompanyCategoryController@index');
            Route::post('/', 'CompanyCategoryController@store');
            Route::delete('/{id}', 'CompanyCategoryController@destroy');
            Route::put('/{id}', 'CompanyCategoryController@update');
        });
        Route::prefix('language_sector')->group(function () {
            Route::get('/', 'LanguageSectorController@index');
            Route::post('/', 'LanguageSectorController@store');
            Route::delete('/{id}', 'LanguageSectorController@destroy');
            Route::put('/{id}', 'LanguageSectorController@update');
        });
    });
});

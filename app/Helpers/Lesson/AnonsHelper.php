<?php

namespace App\Helpers\Lesson;

use App\Models\Lesson\Anons;
use App\User;

class AnonsHelper
{

    public static function getGroupStudentsAndParents($lesson_groups, $type)
    {
        $all_users = [];
        $all_students_id = [];
        $all_parents_id = [];
        foreach ($lesson_groups as $lesson_group) {
            $students = $lesson_group->lesson_group_students->pluck('student');
            $students_id = $students->pluck('id')->toArray();
            $parents = User::join('parent_users as r', 'r.user_id', 'users.id')
                ->join('users as p', 'p.id', 'r.parent_id')
                ->whereIn('users.id', $students_id);
            $parents_id = [];
            if ($type == Anons::PARENTS) {
                $parents = $parents->get(['p.id', 'p.avatar', 'p.fullname']);
                $parents_id = $parents->pluck('id')->toArray();
                $parents = $parents->toArray();
                $students_id = [];
            } else if ($type == Anons::STUDENTS) {
                $parents_id = [];
                $parents = [];
            } else {
                $parents = $parents->get(['p.id', 'p.avatar', 'p.fullname']);
                $parents_id = $parents->pluck('id')->toArray();
                $parents = $parents->toArray();
            }
            $all_students_id = array_merge($all_students_id, $students_id);
            $all_parents_id = array_merge($all_parents_id, $parents_id);
            $users = array_merge($students->toArray(), $parents);
            $all_users = array_merge($all_users, $users);
        }
        return [
            'all_students_id' => $all_students_id,
            'all_parents_id' => $all_parents_id,
            'all_users' => $all_users
        ];
    }
}

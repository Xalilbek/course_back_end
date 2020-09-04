<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User;
        $user->username = 'admin';
        $user->fullname = 'Admin';
        $user->user_type = 'user';
        $user->is_superadmin = 1;
        $user->password = Hash::make('password');
        $user->save();

    }
}

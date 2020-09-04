<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionGroupUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_group_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_group_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('permission_group_id')->references('id')->on('permission_groups')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_group_users');
    }
}

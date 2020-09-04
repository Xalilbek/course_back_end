<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEducationLevelUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('education_level_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('education_level_id');
            $table->unsignedBigInteger('user_id');
            
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('CASCADE');
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
        Schema::dropIfExists('education_level_users');
    }
}

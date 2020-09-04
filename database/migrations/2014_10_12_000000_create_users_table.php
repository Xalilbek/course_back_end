<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->unique()->nullable();
            $table->string('fullname')->nullable();
            $table->string('avatar')->nullable();
            $table->date('birth')->nullable();
            $table->string('mobile_phone')->unique()->nullable();
            $table->dateTime('sms_send_date')->nullable();
            $table->enum('gender',['m','f'])->default('m')->nullable();
            $table->string('mobile_confirm_code')->nullable();
            $table->dateTime('mobile_phone_verified_at')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('user_type',['user','student','teacher','parent'])->nullable();
            $table->text('address')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

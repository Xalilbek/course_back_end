<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonGroupStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_group_students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lesson_group_id');
            $table->unsignedBigInteger('student_id');
            $table->enum('status',['accept','decline','none'])->default('none');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('lesson_group_id')->references('id')->on('lesson_groups')->onDelete('CASCADE');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_group_students');
    }
}

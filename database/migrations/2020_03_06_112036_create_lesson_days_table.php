<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lesson_id');
            $table->date('date');
            $table->unsignedTinyInteger('mark_home')->nullable();
            $table->string('note_home')->nullable();
            $table->unsignedTinyInteger('mark_lesson')->nullable();
            $table->string('note_lesson')->nullable();
            $table->enum('type',['in_time','late','left_earlier','absent'])->default('in_time');
            $table->string('reason')->nullable();
            $table->boolean('parent_seen')->default(0);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_days');
    }
}

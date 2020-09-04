<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnonsGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anons_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('anons_id');
            $table->unsignedBigInteger('lesson_group_id');

            $table->foreign('anons_id')->references('id')->on('anons')->onDelete('CASCADE');
            $table->foreign('lesson_group_id')->references('id')->on('lesson_groups')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anons_groups');
    }
}

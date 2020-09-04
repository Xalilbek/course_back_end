<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageSectorUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language_sector_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('language_sector_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('language_sector_id')->references('id')->on('language_sectors')->onDelete('CASCADE');
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
        Schema::dropIfExists('language_sector_users');
    }
}

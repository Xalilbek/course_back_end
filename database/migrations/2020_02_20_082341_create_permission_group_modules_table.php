<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionGroupModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_group_modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_group_id');
            $table->string('module_name');
            $table->enum('permission_type',['hide','read','full']);
            $table->timestamps();

            $table->foreign('permission_group_id')->references('id')->on('permission_groups')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_group_modules');
    }
}

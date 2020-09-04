<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('user_type',['all','teacher','student','parent']);
            $table->unsignedBigInteger('company_category_id');
            $table->string('title');
            $table->text('description');
            $table->string('image_url');
            $table->decimal('longitude',10,7);
            $table->decimal('latitude',10,7);
            $table->timestamps();

            $table->foreign('company_category_id')->references('id')->on('company_categories')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reporter_id');
            $table->unsignedBigInteger('code');
            $table->string('remark')->nullable();
            $table->integer('status')->default(1);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('reporter_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('code')->references('id')->on('report_codes')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_codes');
        Schema::dropIfExists('reports');
    }
}

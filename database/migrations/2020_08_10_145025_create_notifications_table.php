<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('code');
            $table->integer('type')->default(1); //1=general,2=donate
            $table->integer('status')->default(0);
            $table->boolean('is_readed')->default(0);
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('CASCADE');
            $table->foreign('code')->references('id')->on('notification_codes')->onDelete('CASCADE');
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
        Schema::dropIfExists('notification_codes');
        Schema::dropIfExists('notifications');
    }
}

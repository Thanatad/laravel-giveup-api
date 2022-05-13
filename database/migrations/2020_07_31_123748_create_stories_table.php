<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('file_type')->nullable(); // 1:image, 2:video
            $table->string('file_path')->nullable();
            $table->bigInteger('rotate')->nullable();
            $table->dateTime('expire_time');
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('CASCADE');
            $table->timestamps();
        });


        Schema::create('story_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('story_id');
            $table->foreign('story_id')
            ->references('id')->on('stories')->onDelete('CASCADE');
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('CASCADE');
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
        Schema::dropIfExists('story_logs');
        Schema::dropIfExists('stories');
    }
}

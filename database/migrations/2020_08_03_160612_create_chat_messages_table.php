<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{

    public function up()
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id_one');
            $table->unsignedBigInteger('user_id_two');
            $table->string('name')->nullable();
            $table->integer('type')->default(1); //1=general,2donate
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('post_id')->nullable();
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('CASCADE');
            $table->foreign('user_id_one')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('user_id_two')->references('id')->on('users')->onDelete('CASCADE');
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->text('message')->nullable();
            $table->boolean('is_seen')->default(0);
            $table->integer('type')->default(1); //1=text,2=imge,3=video
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('chat_conversation_id')->references('id')->on('chat_conversations')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
}

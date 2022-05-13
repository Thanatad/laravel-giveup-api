<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{

    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('content')->nullable();
            $table->integer('file_type'); // 1:not file, 2:image, 3:video
            $table->integer('type')->default(1); //1:general, 2:donate
            $table->string('location_name')->nullable();
            $table->boolean('is_comment')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('CASCADE');
            $table->timestamps();
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->bigInteger('parent_id')->nullable();
            $table->text('comment');
            $table->boolean('is_deleted')->default(false);
            $table->foreign('post_id')
                ->references('id')->on('posts')->onDelete('CASCADE');
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('CASCADE');
            $table->timestamps();
        });

        Schema::create('post_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('file_path');
            $table->bigInteger('rotate')->default(0);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('CASCADE');
        });

        Schema::create('post_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('like')->default(1);
            $table->foreign('post_id')
                ->references('id')->on('posts')->onDelete('CASCADE');
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('CASCADE');
            $table->primary(['post_id', 'user_id']);
            $table->timestamps();
        });

        Schema::create('post_donates', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->dateTime('timeout_in');
            $table->integer('status')->default(1); //1:create
            $table->integer('delivery_method'); //1:address owner, 2:address receiver
            $table->unsignedBigInteger('chosen_user')->nullable();
            $table->text('message')->nullable();
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('sub_district')->nullable();
            $table->string('province')->nullable();
            $table->string('postcode')->nullable();
            $table->foreign('chosen_user')
                ->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('post_id')
                ->references('id')->on('posts')->onDelete('CASCADE');
            $table->primary('post_id');
            $table->timestamps();
        });

        Schema::create('post_donate_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->text('reason');
            $table->boolean('is_readed')->default(0);
            $table->foreign('post_id')
                ->references('id')->on('posts')->onDelete('CASCADE');
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('CASCADE');
            $table->timestamps();
        });

        Schema::create('post_donate_object_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('objcategory_id');
            $table->foreign('objcategory_id')->references('id')->on('object_categories')->onDelete('CASCADE');
            $table->foreign('post_id')->references('post_id')->on('post_donates')->onDelete('CASCADE');
            $table->timestamps();
        });

        Schema::create('post_recommends', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('CASCADE');
            $table->primary('post_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_files');
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('post_comments');
        Schema::dropIfExists('post_locations');
        Schema::dropIfExists('post_donate_reasons');
        Schema::dropIfExists('post_donate_object_categories');
        Schema::dropIfExists('post_recommends');
        Schema::dropIfExists('post_donates');
        Schema::dropIfExists('posts');
    }
}

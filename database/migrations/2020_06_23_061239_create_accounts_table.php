<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->string('mobile', 13)->nullable();
            $table->string('name');
            $table->char('gender', 1)->nullable();
            $table->string('about')->nullable();
            $table->text('about_more')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('sub_district')->nullable();
            $table->string('province')->nullable();
            $table->string('postcode')->nullable();
            $table->string('image');
            $table->date('report_end_date')->nullable();
            $table->bigInteger('point')->default(0);
            $table->text('fcm_token');
            $table->primary('user_id');
            $table->timestamps();
        });

        Schema::create('account_attentions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attention_id');
            $table->foreign('attention_id')->references('id')->on('attentions')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
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
        Schema::dropIfExists('account_attentions');
        Schema::dropIfExists('accounts');
    }
}

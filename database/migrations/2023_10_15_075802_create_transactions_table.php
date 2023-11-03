<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id', 32)->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('bet_id');
            $table->string('type');
            $table->integer('amount');
            $table->date('date');
            $table->string('crypto_type');
            $table->string('status');
            $table->string('image');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('bet_id')->references('id')->on('bets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};

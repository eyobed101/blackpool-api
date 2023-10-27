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
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bet_id')->nullable(true);
            $table->enum('type', ['WITHDRAW', 'DEPOSIT']);
            $table->integer('amount');
            $table->string('crypto_type')->nullable(true);
            $table->enum('status', ["SUCCESS", "PENDING", "FAILED"]);
            $table->string('image')->nullable(true);
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

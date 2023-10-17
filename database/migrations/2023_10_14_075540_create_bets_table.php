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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('bet_type', ['SINGLE', 'COMBO']);
            $table->string('event_id');
            $table->unsignedBigInteger('bet_combination_id')->nullable();
            $table->string('outcome');
            $table->integer('bet_amount');
            $table->decimal('potential_payout', 8, 2);
            $table->enum('status', ['PROCESSING', 'COMPLETED', 'FAILED']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            
            $table->foreign('bet_combination_id')->references('id')->on('bet_combinations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bets');
    }
};

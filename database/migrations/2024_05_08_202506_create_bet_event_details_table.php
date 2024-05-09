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
        Schema::create('bet_event_details', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('bet_id')->nullable();
            $table->string('sport');
            $table->decimal('price', 8, 2);
            $table->string('type');
            $table->string('team');
            $table->string('vs')->nullable(); 
            $table->string('home')->nullable();
            $table->string('away')->nullable();
            $table->dateTime('commence_time');
            $table->timestamps();

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
        Schema::dropIfExists('bet_event_details');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->string('id');
            $table->string('sport_key');
            $table->string('sport_title');
            $table->timestamp('commence_time')->nullable()->default(null);
            $table->boolean('completed');
            $table->string('home_team');
            $table->string('away_team');
            $table->json('scores');
            $table->timestamp('last_update')->nullable()->default(null);
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
        Schema::dropIfExists('scores');
    }
};

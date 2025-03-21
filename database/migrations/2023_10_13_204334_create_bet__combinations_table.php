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
        Schema::create('bet_combinations', function (Blueprint $table) {
            $table->string('id', 32)->unique();
            $table->unsignedBigInteger('user_id')->fillable();
            $table->enum('status', ['PROCESSING', 'COMPLETED', 'FAILED']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bet__combinations');
    }
};

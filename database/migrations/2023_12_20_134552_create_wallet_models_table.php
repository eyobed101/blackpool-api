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
        Schema::create('wallet_models', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address')->unique();
            $table->boolean('isCurrent')->default(false);
            $table->string('wallet_qr')->nullable(true);
            $table->string('wallet_name')->unique();
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
        Schema::dropIfExists('wallet_models');
    }
};

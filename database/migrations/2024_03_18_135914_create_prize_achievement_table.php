<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prize_achievement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prize_id');
            $table->unsignedBigInteger('achievement_id');
            $table->integer('level')->default(0);
            $table->timestamps();

            $table->foreign('prize_id')->references('id')->on('prizes')->cascadeOnDelete();
            $table->foreign('achievement_id')->references('id')->on('achievements')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_achievement');
    }
};

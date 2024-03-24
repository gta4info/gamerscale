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
        Schema::create('leaderboard_to_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leaderboard_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('points')->default(0);
            $table->timestamps();

            $table->foreign('leaderboard_id')->references('id')->on('leaderboards')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_to_users');
    }
};

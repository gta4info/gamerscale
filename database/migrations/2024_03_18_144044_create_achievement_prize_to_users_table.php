<?php

use App\Http\Enums\AchievementPrizeUserStatusEnum;
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
        Schema::create('achievement_prize_to_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achievement_id');
            $table->unsignedBigInteger('prize_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('status')->default(AchievementPrizeUserStatusEnum::PENDING->value);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('achievement_id')->references('id')->on('achievements')->cascadeOnDelete();
            $table->foreign('prize_id')->references('id')->on('prizes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_prize_to_users');
    }
};

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
        Schema::table('prize_users', function (Blueprint $table) {
            $table->integer('status')->after('data')->default(\App\Http\Enums\PrizeStatusEnum::PENDING->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prize_users', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
    }
};

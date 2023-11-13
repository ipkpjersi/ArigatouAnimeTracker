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
        Schema::create('user_friends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('friend_user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('show_friend_publicly')->default('true');
            $table->timestamps();

            // Unique constraint to ensure a user cannot add the same friend multiple times
            $table->unique(['user_id', 'friend_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_friends');
    }
};

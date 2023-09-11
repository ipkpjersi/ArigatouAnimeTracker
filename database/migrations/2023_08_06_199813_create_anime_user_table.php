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
        Schema::create('anime_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_id')->constrained('anime')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('watch_status_id')->nullable()->constrained('watch_status')->onDelete('cascade');
            $table->integer('score')->nullable();
            $table->integer('sort_order')->nullable();
            $table->integer('progress')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_user');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The anime table is the base table for our project. It is the most important and largest table we have.
 * The schema for the anime table is modeled after anime-offline-database/anime-offline-database.json
 * The anime-offline-database table is provided manami-project, so thank you to them for providing it.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anime', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('anime_type_id')->constrained('anime_type');
            $table->integer('episodes');
            $table->foreignId('anime_status_id')->constrained('anime_status');
            $table->string('season')->nullable();
            $table->integer('year')->nullable();
            $table->string('picture');
            $table->string('thumbnail');
            $table->text('synonyms')->nullable();
            $table->text('relations')->nullable();
            $table->text('tags')->nullable();
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime');
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('favourites_sort_own', ['title', 'episodes', 'year', 'type', 'status', 'date_added', 'sort_order', 'random'])->default('date_added');
            $table->enum('favourites_sort_own_order', ['asc', 'desc'])->default('desc');
            $table->enum('favourites_sort_others', ['title', 'episodes', 'year', 'type', 'status', 'date_added', 'sort_order', 'random'])->default('date_added');
            $table->enum('favourites_sort_others_order', ['asc', 'desc'])->default('desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'favourites_sort_own',
                'favourites_sort_own_order',
                'favourites_sort_others',
                'favourites_sort_others_order',
            ]);
        });
    }
};

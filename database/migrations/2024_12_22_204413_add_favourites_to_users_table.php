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
        // Add new columns to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('enable_favourites_system')->default(true);
            $table->boolean('show_own_favourites_when_logged_in')->default(true);
            $table->boolean('show_favourites_publicly')->default(true);
            $table->boolean('show_others_favourites')->default(true);
            $table->boolean('show_favourites_in_nav_dropdown')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the columns from the users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'enable_favourites_system',
                'show_own_favourites_when_logged_in',
                'show_favourites_publicly',
                'show_others_favourites',
                'show_favourites_in_nav_dropdown',
            ]);
        });
    }
};

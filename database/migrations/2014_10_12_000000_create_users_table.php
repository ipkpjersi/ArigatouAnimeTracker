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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_moderator')->default(false);
            $table->boolean('dark_mode')->default(true);
            $table->boolean('show_adult_content')->default(false);
            $table->string('avatar')->nullable();
            $table->boolean('is_banned')->default(false);
            $table->integer('anime_list_pagination_size')->default(15);
            $table->boolean('show_anime_list_number')->default(false);
            $table->string('registration_ip')->nullable();
            $table->string('login_ip')->nullable();
            $table->boolean('show_clear_anime_list_button')->default(false);
            $table->boolean('display_anime_cards')->default(true);
            $table->boolean('enable_friends_system')->default(true);
            $table->boolean('show_friends_on_profile_publicly')->default(true);
            $table->boolean('show_friends_on_profile_when_logged_in')->default(true);
            $table->boolean('show_friends_in_nav_dropdown')->default(true);
            $table->boolean('show_friends_on_others_profiles')->default(true);
            $table->boolean('show_anime_notes_publicly')->default(true);
            $table->boolean('enable_reviews_system')->default(true);
            $table->boolean('show_reviews_when_logged_in')->default(true);
            $table->boolean('show_reviews_publicly')->default(true);
            $table->boolean('show_others_reviews')->default(true);
            $table->boolean('show_reviews_in_nav_dropdown')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

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
            $table->boolean('enable_score_charts_system')->default(true);
            $table->boolean('enable_score_charts_own_profile_when_logged_in')->default(true);
            $table->boolean('enable_score_charts_own_profile_publicly')->default(true);
            $table->boolean('enable_score_charts_other_profiles')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('enable_score_charts_system');
            $table->dropColumn('enable_score_charts_own_profile_when_logged_in');
            $table->dropColumn('enable_score_charts_own_profile_publicly');
            $table->dropColumn('enable_score_charts_other_profiles');
        });
    }
};

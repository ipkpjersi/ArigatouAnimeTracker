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
        Schema::table('anime', function (Blueprint $table) {
            // Mirrors api_descriptions_empty: set when a MAL details fetch comes
            // back empty so confirmed-empty anime are demoted to the empty-only
            // retry pass instead of being re-attempted on every normal pass.
            $table->boolean('mal_details_empty')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropColumn('mal_details_empty');
        });
    }
};

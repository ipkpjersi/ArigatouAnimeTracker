<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->boolean('mal_details_downloaded')->default(false);
        });

        // Backfill: treat any anime that already has MAL stats as already
        // downloaded so we don't needlessly re-fetch it. A populated studios
        // list or mal_mean means the MAL fetch already succeeded for that row.
        // Everything left false (anime with a MAL source but no stats yet) is
        // what the download command will now backfill.
        DB::table('anime')
            ->where(function ($query) {
                $query->whereNotNull('studios')
                    ->where('studios', '!=', '')
                    ->where('studios', '!=', '[]');
            })
            ->orWhere(function ($query) {
                $query->whereNotNull('mal_mean')
                    ->where(DB::raw('TRIM(mal_mean)'), '!=', '');
            })
            ->update(['mal_details_downloaded' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropColumn('mal_details_downloaded');
        });
    }
};

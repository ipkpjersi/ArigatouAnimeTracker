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
            $table->string('source')->nullable();
            $table->text('background')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('studios')->nullable();
            $table->string('broadcast')->nullable();
            $table->text('related_anime')->nullable();
            $table->text('related_manga')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropColumn('source');
            $table->dropColumn('background');
            $table->dropColumn('recommendations');
            $table->dropColumn('studios');
            $table->dropColumn('broadcast');
            $table->dropColumn('related_anime');
            $table->dropColumn('related_manga');
        });
    }
};

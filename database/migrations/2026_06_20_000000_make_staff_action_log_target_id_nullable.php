<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Some staff actions (such as merging anime) act on a non-user target, so
     * target_id needs to allow null. The acted-upon entity is recorded in the
     * message for those cases.
     */
    public function up(): void
    {
        Schema::table('staff_action_log', function (Blueprint $table) {
            $table->dropForeign(['target_id']);
        });

        Schema::table('staff_action_log', function (Blueprint $table) {
            $table->unsignedBigInteger('target_id')->nullable()->change();
            $table->foreign('target_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_action_log', function (Blueprint $table) {
            $table->dropForeign(['target_id']);
        });

        Schema::table('staff_action_log', function (Blueprint $table) {
            $table->unsignedBigInteger('target_id')->nullable(false)->change();
            $table->foreign('target_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

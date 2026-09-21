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
        Schema::table('schedules', function (Blueprint $table) {
            $table->decimal('current_latitude', 10, 7)->nullable()->after('status');
            $table->decimal('current_longitude', 10, 7)->nullable()->after('current_latitude');
            $table->integer('current_speed')->nullable()->default(0)->after('current_longitude');
            $table->timestamp('last_location_update')->nullable()->after('current_speed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['current_latitude', 'current_longitude', 'current_speed', 'last_location_update']);
        });
    }
};

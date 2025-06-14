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
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
            DB::statement('ALTER TABLE farmers ADD COLUMN location_coordinates GEOGRAPHY(POINT, 4326)');
            DB::statement('CREATE INDEX farmers_location_idx ON farmers USING GIST(location_coordinates)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            DB::statement('DROP INDEX farmers_location_idx');
            $table->dropColumn('location_coordinates');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });
    }
};

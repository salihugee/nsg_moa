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
        Schema::table('weather_data', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
            DB::statement('ALTER TABLE weather_data ADD COLUMN location GEOGRAPHY(POINT, 4326)');
            DB::statement('CREATE INDEX weather_location_idx ON weather_data USING GIST(location)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weather_data', function (Blueprint $table) {
            DB::statement('DROP INDEX weather_location_idx');
            $table->dropColumn('location');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
        });
    }
};

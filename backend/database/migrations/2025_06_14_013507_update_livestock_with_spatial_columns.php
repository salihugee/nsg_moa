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
        Schema::table('livestock', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
            DB::statement('ALTER TABLE livestock ADD COLUMN location GEOGRAPHY(POINT, 4326)');
            DB::statement('CREATE INDEX livestock_location_idx ON livestock USING GIST(location)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestock', function (Blueprint $table) {
            DB::statement('DROP INDEX livestock_location_idx');
            $table->dropColumn('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });
    }
};

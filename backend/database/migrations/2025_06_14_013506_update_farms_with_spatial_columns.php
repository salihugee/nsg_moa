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
        Schema::table('farms', function (Blueprint $table) {
            $table->dropColumn('coordinates');
            DB::statement('ALTER TABLE farms ADD COLUMN boundaries GEOGRAPHY(POLYGON, 4326)');
            DB::statement('CREATE INDEX farms_boundaries_idx ON farms USING GIST(boundaries)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            DB::statement('DROP INDEX farms_boundaries_idx');
            $table->dropColumn('boundaries');
            $table->json('coordinates')->nullable();
        });
    }
};

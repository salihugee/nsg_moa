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
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('location_coordinates');
            DB::statement('ALTER TABLE projects ADD COLUMN coverage_area GEOGRAPHY(POLYGON, 4326)');
            DB::statement('CREATE INDEX projects_coverage_idx ON projects USING GIST(coverage_area)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            DB::statement('DROP INDEX projects_coverage_idx');
            $table->dropColumn('coverage_area');
            $table->json('location_coordinates')->nullable();
        });
    }
};

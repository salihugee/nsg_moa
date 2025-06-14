<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enablePostGIS();
    }

    protected function enablePostGIS(): void
    {
        // Enable PostGIS extension if not enabled
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
    }

    protected function createPoint(float $lat, float $lng): string
    {
        return "ST_SetSRID(ST_MakePoint($lng, $lat), 4326)";
    }

    protected function createPolygon(array $coordinates): string
    {
        $points = array_map(function ($coord) {
            return "{$coord[0]} {$coord[1]}";
        }, $coordinates);
        
        $pointString = implode(',', $points);
        return "ST_SetSRID(ST_GeomFromText('POLYGON(($pointString))'), 4326)";
    }

    protected function assertGeometryEquals($expected, $actual, string $message = ''): void
    {
        $result = DB::selectOne("SELECT ST_Equals($expected, $actual) as equals");
        $this->assertTrue($result->equals, $message ?: 'Geometries are not equal');
    }
}

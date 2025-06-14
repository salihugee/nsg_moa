<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Farm;
use App\Models\Farmer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class FarmTest extends TestCase
{
    use RefreshDatabase;

    protected $farm;
    protected $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test farmer
        $this->farmer = Farmer::create([
            'registration_number' => 'F123456',
            'full_name' => 'Test Farmer',
            'phone_number' => '1234567890',
            'location' => DB::raw($this->createPoint(9.0579, 7.4951)),
            'farm_size' => 5.5,
            'registration_date' => now(),
            'status' => 'active'
        ]);

        // Create test farm with boundaries
        $boundaries = $this->createPolygon([
            [7.4951, 9.0579],
            [7.4961, 9.0579],
            [7.4961, 9.0589],
            [7.4951, 9.0589],
            [7.4951, 9.0579]
        ]);

        $this->farm = Farm::create([
            'farmer_id' => $this->farmer->id,
            'name' => 'Test Farm',
            'boundaries' => DB::raw($boundaries),
            'soil_type' => 'loamy',
            'water_source' => 'river'
        ]);
    }

    /** @test */
    public function it_can_create_farm_with_boundaries()
    {
        $boundaries = $this->createPolygon([
            [7.4961, 9.0589],
            [7.4971, 9.0589],
            [7.4971, 9.0599],
            [7.4961, 9.0599],
            [7.4961, 9.0589]
        ]);

        $farm = Farm::create([
            'farmer_id' => $this->farmer->id,
            'name' => 'Another Farm',
            'boundaries' => DB::raw($boundaries),
            'soil_type' => 'clay',
            'water_source' => 'borehole'
        ]);

        $this->assertDatabaseHas('farms', [
            'name' => 'Another Farm',
            'soil_type' => 'clay'
        ]);

        $this->assertGeometryEquals(
            $boundaries,
            $farm->getRawOriginal('boundaries')
        );
    }

    /** @test */
    public function it_can_calculate_area()
    {
        $area = $this->farm->calculateArea();
        $this->assertIsFloat($area);
        $this->assertTrue($area > 0);
    }

    /** @test */
    public function it_can_find_farms_in_area()
    {
        $searchArea = $this->createPolygon([
            [7.4941, 9.0569],
            [7.4971, 9.0569],
            [7.4971, 9.0599],
            [7.4941, 9.0599],
            [7.4941, 9.0569]
        ]);

        $farmsInArea = Farm::inArea($searchArea)->get();
        $this->assertEquals(1, $farmsInArea->count());
    }

    /** @test */
    public function it_can_update_size_from_boundaries()
    {
        $originalSize = $this->farm->size_hectares;
        $this->farm->updateSizeFromBoundaries();
        $this->assertNotEquals($originalSize, $this->farm->size_hectares);
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Farmer;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FarmerTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and farmer
        $role = Role::create(['name' => 'farmer']);
        $this->user = User::create([
            'name' => 'Test Farmer',
            'email' => 'test@farmer.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id
        ]);

        $this->farmer = Farmer::create([
            'user_id' => $this->user->id,
            'registration_number' => 'F123456',
            'full_name' => 'Test Farmer',
            'phone_number' => '1234567890',
            'location' => DB::raw($this->createPoint(9.0579, 7.4951)), // Abuja coordinates
            'farm_size' => 5.5,
            'registration_date' => now(),
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_can_create_farmer_with_location()
    {
        $location = DB::raw($this->createPoint(9.0579, 7.4951));
        
        $farmer = Farmer::create([
            'user_id' => $this->user->id,
            'registration_number' => 'F123457',
            'full_name' => 'Another Farmer',
            'phone_number' => '0987654321',
            'location' => $location,
            'farm_size' => 3.2,
            'registration_date' => now(),
            'status' => 'active'
        ]);

        $this->assertDatabaseHas('farmers', [
            'registration_number' => 'F123457',
            'full_name' => 'Another Farmer'
        ]);

        $this->assertGeometryEquals(
            $location,
            $farmer->getRawOriginal('location')
        );
    }

    /** @test */
    public function it_can_find_nearby_farmers()
    {
        // Create another farmer 1km away
        Farmer::create([
            'user_id' => $this->user->id,
            'registration_number' => 'F123458',
            'full_name' => 'Nearby Farmer',
            'phone_number' => '1122334455',
            'location' => DB::raw($this->createPoint(9.0579, 7.4961)), // ~1km away
            'farm_size' => 2.5,
            'registration_date' => now(),
            'status' => 'active'
        ]);

        // Create a farmer 10km away
        Farmer::create([
            'user_id' => $this->user->id,
            'registration_number' => 'F123459',
            'full_name' => 'Far Farmer',
            'phone_number' => '5544332211',
            'location' => DB::raw($this->createPoint(9.1579, 7.5951)), // ~10km away
            'farm_size' => 4.0,
            'registration_date' => now(),
            'status' => 'active'
        ]);

        $nearbyFarmers = Farmer::nearby(9.0579, 7.4951, 5000)->get(); // 5km radius
        $this->assertEquals(2, $nearbyFarmers->count());
    }

    /** @test */
    public function it_can_find_farmers_in_area()
    {
        $polygon = $this->createPolygon([
            [7.4951, 9.0579],
            [7.4961, 9.0579],
            [7.4961, 9.0589],
            [7.4951, 9.0589],
            [7.4951, 9.0579]
        ]);

        $farmersInArea = Farmer::inArea($polygon)->get();
        $this->assertEquals(1, $farmersInArea->count());
    }
}

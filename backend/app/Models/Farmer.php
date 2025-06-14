<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSpatialAttributes;

/**
 * @OA\Schema(
 *     schema="Farmer",
 *     type="object",
 *     description="Farmer model",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="registration_number", type="string"),
 *     @OA\Property(property="full_name", type="string"),
 *     @OA\Property(property="phone_number", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="location_geojson", type="object"),
 *     @OA\Property(property="farm_size", type="number", format="float"),
 *     @OA\Property(property="registration_date", type="string", format="date"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="primary_crop", type="string"),
 *     @OA\Property(property="years_farming", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Farmer extends Model
{
    use HasSpatialAttributes;

    protected $geometryColumn = 'location';

    protected $fillable = [
        'registration_number',
        'full_name',
        'phone_number',
        'email',
        'location',
        'farm_size',
        'registration_date',
        'status',
        'user_id',
        'address',
        'primary_crop',
        'years_farming'
    ];

    protected $casts = [
        'registration_date' => 'date',
        'farm_size' => 'decimal:2',
        'years_farming' => 'integer'
    ];

    protected $appends = [
        'location_geojson'
    ];

    public function getLocationGeoJsonAttribute()
    {
        return $this->getSpatialAttribute('location');
    }

    public function setLocationAttribute($value)
    {
        if (is_array($value) && isset($value['lat']) && isset($value['lng'])) {
            $this->attributes['location'] = $this->pointFromLatLng($value['lat'], $value['lng']);
        } else {
            $this->setSpatialAttribute('location', $value);
        }
    }

    /**
     * Scope to find farmers within a certain distance
     */
    public function scopeNearby($query, $lat, $lng, $distance)
    {
        return $query->whereRaw("ST_DWithin(
            location,
            ST_SetSRID(ST_MakePoint(?, ?), 4326),
            ?
        )", [$lng, $lat, $distance]);
    }

    /**
     * Scope to find farmers in a specific area (polygon)
     */
    public function scopeInArea($query, $geojson)
    {
        return $query->whereRaw("ST_Within(location, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geojson]);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function farms()
    {
        return $this->hasMany(Farm::class);
    }

    public function livestock()
    {
        return $this->hasMany(Livestock::class);
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }
}

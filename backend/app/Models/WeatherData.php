<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSpatialAttributes;

class WeatherData extends Model
{
    use HasSpatialAttributes;

    protected $geometryColumn = 'location';

    protected $fillable = [
        'location',
        'temperature',
        'rainfall',
        'humidity',
        'wind_speed',
        'wind_direction',
        'pressure',
        'solar_radiation',
        'soil_moisture',
        'recorded_at'
    ];

    protected $spatialFields = [
        'location'
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'rainfall' => 'decimal:2',
        'humidity' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'wind_direction' => 'decimal:2',
        'pressure' => 'decimal:2',
        'solar_radiation' => 'decimal:2',
        'soil_moisture' => 'decimal:2',
        'recorded_at' => 'datetime'
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
     * Scope to find weather data within a region
     */
    public function scopeInRegion($query, $geojson)
    {
        return $query->whereRaw("ST_Within(location, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geojson]);
    }

    /**
     * Scope to find weather data within a certain distance
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
     * Scope to get the latest weather data for each location
     */
    public function scopeLatest($query)
    {
        return $query->whereIn('id', function($q) {
            $q->select(\DB::raw('MAX(id)'))
                ->from('weather_data')
                ->groupBy('location');
        });
    }

    /**
     * Get weather alerts based on thresholds
     */
    public static function getAlerts($thresholds = null)
    {
        if (!$thresholds) {
            $thresholds = [
                'rainfall' => 50, // mm
                'wind_speed' => 20, // km/h
                'temperature' => 35 // °C
            ];
        }

        return static::latest()
            ->where(function($query) use ($thresholds) {
                $query->where('rainfall', '>', $thresholds['rainfall'])
                    ->orWhere('wind_speed', '>', $thresholds['wind_speed'])
                    ->orWhere('temperature', '>', $thresholds['temperature']);
            })
            ->get();
    }

    /**
     * Get average weather data for a region and time period
     */
    public static function getRegionalAverage($geojson, $startDate, $endDate)
    {
        return static::selectRaw('
            AVG(temperature) as avg_temperature,
            AVG(rainfall) as avg_rainfall,
            AVG(humidity) as avg_humidity,
            AVG(wind_speed) as avg_wind_speed
        ')
        ->inRegion($geojson)
        ->whereBetween('recorded_at', [$startDate, $endDate])
        ->first();
    }
}

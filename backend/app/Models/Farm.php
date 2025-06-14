<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSpatialAttributes;

class Farm extends Model
{
    use HasSpatialAttributes;

    protected $geometryColumn = 'boundaries';

    protected $fillable = [
        'farmer_id',
        'name',
        'size_hectares',
        'soil_type',
        'water_source',
        'boundaries'
    ];

    protected $spatialFields = [
        'boundaries'
    ];

    protected $casts = [
        'size_hectares' => 'decimal:2'
    ];

    protected $appends = [
        'boundaries_geojson'
    ];

    public function getBoundariesGeoJsonAttribute()
    {
        return $this->getSpatialAttribute('boundaries');
    }

    public function setBoundariesAttribute($value)
    {
        $this->setSpatialAttribute('boundaries', $value);
    }

    /**
     * Scope to find farms that intersect with a given area
     */
    public function scopeInArea($query, $geojson)
    {
        return $query->whereRaw("ST_Intersects(boundaries, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geojson]);
    }

    /**
     * Calculate the actual area in hectares
     */
    public function calculateArea()
    {
        $result = DB::select("SELECT ST_Area(ST_Transform(boundaries, 3857))/10000 as hectares FROM farms WHERE id = ?", [$this->id]);
        return round($result[0]->hectares, 2);
    }

    /**
     * Update the size_hectares based on the boundaries
     */
    public function updateSizeFromBoundaries()
    {
        $this->size_hectares = $this->calculateArea();
        $this->save();
    }

    // Relationships
    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function crops()
    {
        return $this->hasMany(Crop::class);
    }
}

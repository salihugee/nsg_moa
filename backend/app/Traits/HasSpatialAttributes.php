<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasSpatialAttributes
{
    /**
     * Get a spatial attribute
     */
    public function getSpatialAttribute($key)
    {
        if (!$this->exists) {
            return null;
        }

        $value = $this->attributes[$key] ?? null;

        if ($value === null) {
            return null;
        }

        $result = DB::select("SELECT ST_AsGeoJSON(:value) AS geojson", ['value' => $value]);
        return json_decode($result[0]->geojson);
    }

    /**
     * Set a spatial attribute
     */
    public function setSpatialAttribute($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        $this->attributes[$key] = DB::raw("ST_GeomFromGeoJSON('$value')");
    }

    /**
     * Convert coordinates to point
     */
    protected function pointFromLatLng($lat, $lng)
    {
        return DB::raw("ST_SetSRID(ST_MakePoint($lng, $lat), 4326)");
    }

    /**
     * Convert GeoJSON to geometry
     */
    protected function geomFromGeoJSON($geojson)
    {
        if (is_array($geojson) || is_object($geojson)) {
            $geojson = json_encode($geojson);
        }
        return DB::raw("ST_SetSRID(ST_GeomFromGeoJSON('$geojson'), 4326)");
    }

    /**
     * Calculate distance between two points in meters
     */
    protected function distanceTo($geometry)
    {
        return DB::raw("ST_Distance(
            {$this->getGeometryColumn()},
            ST_SetSRID(ST_GeomFromGeoJSON('$geometry'), 4326)
        )");
    }

    /**
     * Find entities within a certain distance
     */
    public function scopeWithinDistance($query, $geometry, $distance)
    {
        return $query->whereRaw("ST_DWithin(
            {$this->getGeometryColumn()},
            ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
            ?
        )", [$geometry, $distance]);
    }

    /**
     * Find entities within a polygon
     */
    public function scopeWithinArea($query, $polygon)
    {
        return $query->whereRaw("ST_Within(
            {$this->getGeometryColumn()},
            ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)
        )", [$polygon]);
    }

    /**
     * Get the name of the geometry column
     */
    protected function getGeometryColumn()
    {
        return $this->geometryColumn ?? 'geometry';
    }

    /**
     * Convert geometry to GeoJSON
     */
    protected function toGeoJSON($geometry)
    {
        $result = DB::select("SELECT ST_AsGeoJSON(?) AS geojson", [$geometry]);
        return json_decode($result[0]->geojson);
    }
}

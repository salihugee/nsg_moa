<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSpatialAttributes;

class Project extends Model
{
    use HasSpatialAttributes;

    protected $geometryColumn = 'coverage_area';

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'budget',
        'status',
        'coverage_area',
        'target_beneficiaries',
        'actual_beneficiaries',
        'project_manager',
        'implementing_partners'
    ];

    protected $spatialFields = [
        'coverage_area'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'target_beneficiaries' => 'integer',
        'actual_beneficiaries' => 'integer',
        'implementing_partners' => 'array'
    ];

    protected $appends = [
        'coverage_area_geojson',
        'duration_months',
        'progress_percentage'
    ];

    public function getCoverageAreaGeoJsonAttribute()
    {
        return $this->getSpatialAttribute('coverage_area');
    }

    public function setCoverageAreaAttribute($value)
    {
        $this->setSpatialAttribute('coverage_area', $value);
    }

    public function getDurationMonthsAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInMonths($this->end_date);
        }
        return null;
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->start_date && $this->end_date && $this->start_date->isPast()) {
            $total = $this->start_date->diffInDays($this->end_date);
            $elapsed = $this->start_date->diffInDays(now());
            return min(100, round(($elapsed / $total) * 100));
        }
        return 0;
    }

    /**
     * Scope to find projects that intersect with a given area
     */
    public function scopeInArea($query, $geojson)
    {
        return $query->whereRaw("ST_Intersects(coverage_area, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))", [$geojson]);
    }

    /**
     * Calculate the coverage area in hectares
     */
    public function calculateCoverageArea()
    {
        $result = DB::select("SELECT ST_Area(ST_Transform(coverage_area, 3857))/10000 as hectares FROM projects WHERE id = ?", [$this->id]);
        return round($result[0]->hectares, 2);
    }

    /**
     * Get all farmers within the project area
     */
    public function getFarmersInArea()
    {
        return Farmer::whereRaw("ST_Within(location, ST_Transform(?, 4326))", [$this->coverage_area])->get();
    }

    /**
     * Get all farms within the project area
     */
    public function getFarmsInArea()
    {
        return Farm::whereRaw("ST_Intersects(boundaries, ST_Transform(?, 4326))", [$this->coverage_area])->get();
    }

    // Relationships
    public function metrics()
    {
        return $this->hasMany(ProjectMetric::class);
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }
}

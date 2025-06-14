<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $fillable = [
        'farm_id',
        'crop_type',
        'planting_date',
        'expected_harvest_date',
        'actual_harvest_date',
        'yield_quantity',
        'status'
    ];

    protected $casts = [
        'planting_date' => 'date',
        'expected_harvest_date' => 'date',
        'actual_harvest_date' => 'date',
        'yield_quantity' => 'decimal:2'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}

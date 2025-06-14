<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSpatialAttributes;

class Livestock extends Model
{
    use HasSpatialAttributes;

    protected $fillable = [
        'farmer_id',
        'animal_type',
        'quantity',
        'health_status',
        'location'
    ];

    protected $spatialFields = [
        'location'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
}

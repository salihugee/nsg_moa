<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMetric extends Model
{
    protected $fillable = [
        'project_id',
        'metric_name',
        'target_value',
        'current_value',
        'last_updated'
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'last_updated' => 'datetime'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

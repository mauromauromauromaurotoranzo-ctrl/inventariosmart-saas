<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingProgress extends Model
{
    protected $table = 'onboarding_progress';
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'tenant_id',
        'current_step',
        'completed_steps',
        'step_data',
        'started_at',
        'completed_at',
    ];
    
    protected $casts = [
        'completed_steps' => 'array',
        'step_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}

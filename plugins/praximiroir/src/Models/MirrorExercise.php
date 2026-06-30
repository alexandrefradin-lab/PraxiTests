<?php

namespace Praxis\Plugins\PraxiMiroir\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MirrorExercise extends Model
{
    protected $table = 'mirror_exercises';

    protected $fillable = [
        'day_index',
        'bloc',
        'title',
        'summary',
        'body',
        'prompt',
        'duration_min',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'day_index'    => 'integer',
        'duration_min' => 'integer',
        'is_active'    => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('day_index');
    }
}

<?php

namespace Praxis\Plugins\PraxiBoost\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DevExercise extends Model
{
    protected $table = 'dev_exercises';

    protected $fillable = [
        'slug',
        'title',
        'category',
        'summary',
        'body',
        'duration_min',
        'icon',
        'threshold_eclats',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'duration_min'     => 'integer',
        'threshold_eclats' => 'integer',
        'sort_order'       => 'integer',
        'is_active'        => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('threshold_eclats')->orderBy('sort_order');
    }
}

<?php

namespace Praxis\Plugins\PraxiLead\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MgmtPractice extends Model
{
    protected $table = 'mgmt_practices';

    protected $fillable = [
        'day_index',
        'theme',
        'title',
        'summary',
        'body',
        'micro_challenge',
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

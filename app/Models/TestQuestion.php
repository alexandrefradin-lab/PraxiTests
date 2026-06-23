<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestion extends Model
{
    // cf. audit E-6 — $fillable explicite (protection mass assignment)
    protected $fillable = [
        'section_id', 'order', 'type', 'prompt', 'helper',
        'options', 'validation', 'scoring', 'required', 'meta',
    ];

    protected $casts = [
        'options'    => 'array',
        'validation' => 'array',
        'scoring'    => 'array',
        'meta'       => 'array',
        'required'   => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TestSection::class, 'section_id');
    }
}

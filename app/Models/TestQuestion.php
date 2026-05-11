<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options'    => 'array',
        'validation' => 'array',
        'scoring'    => 'array',
        'required'   => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TestSection::class, 'section_id');
    }
}

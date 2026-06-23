<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSection extends Model
{
    // cf. audit E-6 — $fillable explicite (protection mass assignment)
    protected $fillable = [
        'test_id', 'order', 'title', 'description',
        'narrative_intro', 'narrative_outro', 'config',
    ];
    protected $casts = ['config' => 'array'];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class, 'section_id')->orderBy('order');
    }
}

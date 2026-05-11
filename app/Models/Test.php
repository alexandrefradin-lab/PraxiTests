<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'scoring_config'  => 'array',
        'gamification'    => 'array',
        'neuromarketing'  => 'array',
        'metadata'        => 'array',
        'published'       => 'boolean',
        'public'          => 'boolean',
    ];

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(TestSection::class)->orderBy('order');
    }

    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(TestQuestion::class, TestSection::class, 'test_id', 'section_id')
            ->orderBy('test_sections.order')->orderBy('test_questions.order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function totalQuestions(): int
    {
        return $this->questions()->count();
    }
}

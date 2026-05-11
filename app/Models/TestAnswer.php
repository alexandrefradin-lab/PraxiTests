<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    protected $guarded = [];
    protected $casts = ['value' => 'array'];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }
}

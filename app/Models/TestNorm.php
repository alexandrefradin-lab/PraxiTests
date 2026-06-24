<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestNorm extends Model
{
    protected $table = 'test_norms';

    protected $fillable = [
        'test_slug',
        'dimension',
        'group_key',
        'n_responses',
        'mean',
        'std_dev',
        'source',
        'computed_at',
    ];

    protected $casts = [
        'mean'        => 'float',
        'std_dev'     => 'float',
        'n_responses' => 'integer',
        'computed_at' => 'datetime',
    ];
}

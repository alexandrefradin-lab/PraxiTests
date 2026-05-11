<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $guarded = [];

    protected $casts = [
        'manifest'          => 'array',
        'config'            => 'array',
        'permissions'       => 'array',
        'enabled'           => 'boolean',
        'core'              => 'boolean',
        'installed_at'      => 'datetime',
        'last_activated_at' => 'datetime',
    ];

    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->manifest['_path'] ?? null,
        );
    }
}

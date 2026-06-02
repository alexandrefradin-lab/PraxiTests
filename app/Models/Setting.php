<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    // ── Lecture ──────────────────────────────────────────────────────────────

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $row = Cache::remember("settings.{$group}.{$key}", 300, function () use ($group, $key) {
            return static::where('group', $group)->where('key', $key)->first();
        });
        if (!$row) return $default;

        return $row->encrypted ? Crypt::decryptString($row->value) : $row->value;
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->get()
            ->mapWithKeys(fn ($row) => [
                $row->key => $row->encrypted
                    ? Crypt::decryptString($row->value)
                    : $row->value,
            ])->all();
    }

    // ── Écriture ─────────────────────────────────────────────────────────────

    public static function set(string $group, string $key, mixed $value, bool $encrypt = false): void
    {
        $stored = ($value !== null && $encrypt) ? Crypt::encryptString((string) $value) : $value;

        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'encrypted' => $encrypt]
        );

        Cache::forget("settings.{$group}.{$key}");
    }

    public static function setMany(string $g
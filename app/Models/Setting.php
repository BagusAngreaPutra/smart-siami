<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("setting:{$key}", fn (): ?string => self::query()->where('key', $key)->value('value') ?? $default);
    }

    public static function putValue(string $key, ?string $value): self
    {
        Cache::forget("setting:{$key}");

        return self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

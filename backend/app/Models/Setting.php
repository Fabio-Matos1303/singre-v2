<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key','value'];

    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("setting:{$key}", 300, function () use ($key, $default) {
            return optional(static::query()->where('key',$key)->first())->value ?? $default;
        });
    }

    public static function setValue(string $key, $value): void
    {
        static::query()->updateOrCreate(['key'=>$key], ['value'=>$value]);
        Cache::forget("setting:{$key}");
    }

    public static function intValue(string $key, int $default = 0): int
    {
        return (int) static::getValue($key, $default);
    }
}

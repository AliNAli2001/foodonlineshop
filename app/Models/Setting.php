<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'dollar_exchange_rate',
        'general_minimum_alert_quantity',
        'max_order_items',
    ];

    protected $casts = [
        'dollar_exchange_rate' => 'decimal:4',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the singleton instance of settings.
     */
    public static function getInstance(): self
    {
        return self::firstOrCreate(['id' => 1]);
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::getInstance();
        return $setting->{$key} ?? $default;
    }

    /**
     * Update a setting value.
     */
    public static function updateSetting(string $key, $value): void
    {
        $setting = self::getInstance();
        $setting->update([$key => $value]);
    }
}


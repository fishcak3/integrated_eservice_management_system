<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrgySetting extends Model
{
    // If your table is 'brgy_settings', Laravel finds it automatically.
    // If you used a different name, define: protected $table = 'your_table_name';

    protected $fillable = ['key', 'value'];

    // Helper to get a setting value
    public static function get($key, $default = null)
    {
        return self::where('key', $key)->value('value') ?? $default;
    }

    // Helper to save a setting value
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
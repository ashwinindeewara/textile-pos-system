<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getAllSettings(): array
    {
        $defaults = [
            'shop_name' => 'SILK & DENIM',
            'shop_address' => '123 Fashion Street, Colombo',
            'phone_number' => '+94 11 234 5678',
            'currency_symbol' => 'LKR',
            'logo_path' => null,
            'receipt_footer' => 'Exchanges allowed within 7 days with bill. Thank you for shopping with us!',
        ];

        try {
            $settings = static::pluck('value', 'key')->toArray();
            return array_merge($defaults, $settings);
        } catch (\Throwable $e) {
            return $defaults;
        }
    }
}

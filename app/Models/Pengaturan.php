<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'mahad_pengaturan';

    protected $fillable = [
        'key',
        'value',
        'kategori',
    ];

    public static function get(string $key, $default = null)
    {
        $item = static::where('key', $key)->first();
        return $item ? $item->value : $default;
    }

    public static function set(string $key, $value, string $kategori = 'umum'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'kategori' => $kategori]
        );
    }
}

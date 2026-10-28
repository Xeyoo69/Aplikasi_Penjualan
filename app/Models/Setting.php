<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'store_name',
        'address',
        'phone',
        'tax_percent',
        'receipt_footer',
    ];

    protected function casts(): array
    {
        return [
            'tax_percent' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'store_name' => 'Toko Saya',
            'address' => null,
            'phone' => null,
            'tax_percent' => 0,
            'receipt_footer' => 'Terima kasih telah berbelanja.',
        ]);
    }
}

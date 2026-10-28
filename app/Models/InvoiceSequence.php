<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    protected $primaryKey = 'date';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'date',
        'last_number',
    ];

    protected function casts(): array
    {
        // Tanggal sengaja tidak di-cast agar nilainya selalu 'Y-m-d' dan
        // cocok dengan primary key saat query lockForUpdate.
        return [
            'last_number' => 'integer',
        ];
    }
}

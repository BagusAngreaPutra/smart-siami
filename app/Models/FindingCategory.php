<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FindingCategory extends Model
{
    protected $fillable = [
        'nama',
        'warna_hex',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }
}

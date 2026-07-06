<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'jenis_unit',
        'fakultas_induk',
        'nama_pimpinan',
        'email',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function auditAssignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class);
    }

    public function hasActiveAssignments(): bool
    {
        return $this->auditAssignments()->where('status', 'aktif')->exists();
    }

    public static function jenisUnitOptions(): array
    {
        return [
            'fakultas' => 'Fakultas',
            'prodi' => 'Program Studi',
            'unit_kerja' => 'Unit Kerja',
            'lainnya' => 'Lainnya',
        ];
    }
}

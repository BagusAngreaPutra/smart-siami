<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Finding extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_temuan',
        'assignment_id',
        'standard_id',
        'instrument_id',
        'kategori',
        'prioritas',
        'kondisi_aktual',
        'kriteria',
        'bukti_objektif',
        'akar_masalah_awal',
        'rekomendasi_auditor',
        'target_penyelesaian',
        'status',
        'alasan_pembatalan',
        'dibuat_oleh',
        'difinalisasi_oleh',
        'waktu_finalisasi',
    ];

    protected function casts(): array
    {
        return [
            'target_penyelesaian' => 'date',
            'waktu_finalisasi' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'difinalisasi_oleh');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(FindingStatusHistory::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function latestFollowUp(): HasOne
    {
        return $this->hasOne(FollowUp::class)->latestOfMany();
    }

    public function hasFollowUps(): bool
    {
        return $this->followUps()->exists();
    }

    public function isEditableFreely(): bool
    {
        return $this->status === 'draft';
    }

    public static function kategoriOptions(): array
    {
        return [
            'observasi' => 'Observasi',
            'peluang_peningkatan' => 'Peluang Peningkatan',
            'minor' => 'Minor',
            'mayor' => 'Mayor',
        ];
    }

    public static function prioritasOptions(): array
    {
        return [
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'aktif' => 'Aktif',
            'dalam_tindak_lanjut' => 'Dalam Tindak Lanjut',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'ditutup' => 'Ditutup',
            'terlambat' => 'Terlambat',
            'dibatalkan' => 'Dibatalkan',
        ];
    }
}

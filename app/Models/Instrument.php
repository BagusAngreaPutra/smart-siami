<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'standard_id',
        'nama_indikator',
        'pertanyaan',
        'jenis_jawaban',
        'target_kriteria',
        'bobot',
        'panduan_pengisian',
        'bukti_diperlukan',
        'opsi_jawaban',
        'skor_min',
        'skor_max',
        'kombinasi_jawaban',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
            'opsi_jawaban' => 'array',
            'skor_min' => 'integer',
            'skor_max' => 'integer',
            'kombinasi_jawaban' => 'array',
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function selfAssessments(): HasMany
    {
        return $this->hasMany(SelfAssessment::class);
    }

    public function hasBeenUsedInActiveAuditPeriod(): bool
    {
        return false;
    }

    public static function jenisJawabanOptions(): array
    {
        return [
            'narasi' => 'Narasi',
            'angka' => 'Angka',
            'pilihan' => 'Pilihan',
            'skor' => 'Skor',
            'unggah_file' => 'Unggah File',
            'kombinasi' => 'Kombinasi',
        ];
    }

    public static function kombinasiOptions(): array
    {
        return [
            'narasi' => 'Narasi',
            'angka' => 'Angka',
            'pilihan' => 'Pilihan',
            'skor' => 'Skor',
            'unggah_file' => 'Unggah File',
        ];
    }
}

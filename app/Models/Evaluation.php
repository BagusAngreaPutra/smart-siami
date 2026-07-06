<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'instrument_id',
        'self_assessment_id',
        'skor',
        'status_bukti',
        'catatan_auditor',
        'usulan_temuan',
        'rekomendasi_awal',
        'status_pemeriksaan',
        'diperiksa_oleh',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'decimal:2',
            'usulan_temuan' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function selfAssessment(): BelongsTo
    {
        return $this->belongsTo(SelfAssessment::class);
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh');
    }

    public static function statusBuktiOptions(): array
    {
        return [
            'belum_diperiksa' => 'Belum Diperiksa',
            'valid' => 'Valid',
            'perlu_klarifikasi' => 'Perlu Klarifikasi',
            'tidak_tersedia' => 'Tidak Tersedia',
        ];
    }

    public static function statusPemeriksaanOptions(): array
    {
        return [
            'belum_dimulai' => 'Belum Dimulai',
            'berlangsung' => 'Berlangsung',
            'menunggu_klarifikasi' => 'Menunggu Klarifikasi',
            'final' => 'Final',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SelfAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'instrument_id',
        'jawaban_naratif',
        'realisasi',
        'target',
        'kendala',
        'analisis_gap',
        'rencana_perbaikan_awal',
        'status',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['dikirim', 'final'], true);
    }

    public static function statusOptions(): array
    {
        return [
            'belum_diisi' => 'Belum Diisi',
            'draft' => 'Draft',
            'dikirim' => 'Dikirim',
            'perlu_klarifikasi' => 'Perlu Klarifikasi',
            'final' => 'Final',
        ];
    }
}

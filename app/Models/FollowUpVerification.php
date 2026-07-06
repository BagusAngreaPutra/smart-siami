<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpVerification extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'follow_up_id',
        'verifikator_id',
        'keputusan',
        'catatan_verifikasi',
        'waktu_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'waktu_verifikasi' => 'datetime',
        ];
    }

    public function followUp(): BelongsTo
    {
        return $this->belongsTo(FollowUp::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public static function keputusanOptions(): array
    {
        return [
            'disetujui' => 'Disetujui',
            'perlu_perbaikan' => 'Perlu Perbaikan',
            'ditolak' => 'Ditolak',
        ];
    }
}

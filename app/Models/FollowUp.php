<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'finding_id',
        'assignment_id',
        'rencana_tindakan',
        'penanggung_jawab',
        'target_penyelesaian',
        'indikator_keberhasilan',
        'progres',
        'kendala',
        'catatan_auditee',
        'status',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'target_penyelesaian' => 'date',
        ];
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'follow_up_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(FollowUpVerification::class);
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(FollowUpVerification::class)->latestOfMany('waktu_verifikasi');
    }

    public function canBeEditedByAuditee(): bool
    {
        return in_array($this->status, ['draft', 'perlu_perbaikan'], true);
    }

    public static function progresOptions(): array
    {
        return [
            'belum_mulai' => 'Belum Mulai',
            'berlangsung' => 'Berlangsung',
            'selesai' => 'Selesai',
            'terkendala' => 'Terkendala',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'belum_dibuat' => 'Belum Dibuat',
            'draft' => 'Draft',
            'diajukan' => 'Diajukan',
            'perlu_perbaikan' => 'Perlu Perbaikan',
            'disetujui' => 'Disetujui',
        ];
    }
}

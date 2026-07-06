<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tahun_akademik',
        'jenis_audit',
        'tanggal_mulai',
        'batas_evaluasi_diri',
        'batas_desk_evaluation',
        'visitasi_mulai',
        'visitasi_selesai',
        'batas_tindak_lanjut',
        'status',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'batas_evaluasi_diri' => 'date',
            'batas_desk_evaluation' => 'date',
            'visitasi_mulai' => 'date',
            'visitasi_selesai' => 'date',
            'batas_tindak_lanjut' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class);
    }

    public function canBeEdited(): bool
    {
        return $this->status !== 'diarsipkan';
    }

    public function canActivate(): bool
    {
        return $this->status === 'draft';
    }

    public function canClose(): bool
    {
        return $this->status === 'aktif';
    }

    public function canArchive(): bool
    {
        return $this->status === 'ditutup';
    }

    public function hasOpenFindings(): bool
    {
        return $this->assignments()
            ->whereHas('findings', fn ($query) => $query->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat']))
            ->exists();
    }

    /**
     * @return array{assignments: int, self_evaluation_progress: int, active_findings: int}
     */
    public function summary(): array
    {
        return [
            'assignments' => $this->assignments()->where('status', 'aktif')->count(),
            'self_evaluation_progress' => 0,
            'active_findings' => Finding::query()
                ->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $this->id))
                ->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat'])
                ->count(),
        ];
    }

    public static function jenisAuditOptions(): array
    {
        return [
            'reguler' => 'Reguler',
            'akademik' => 'Akademik',
            'nonakademik' => 'Nonakademik',
            'tindak_lanjut' => 'Tindak Lanjut',
            'khusus' => 'Khusus',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'aktif' => 'Aktif',
            'ditutup' => 'Ditutup',
            'diarsipkan' => 'Diarsipkan',
        ];
    }
}

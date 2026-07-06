<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AuditAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_period_id',
        'unit_id',
        'lead_auditor_id',
        'catatan_penugasan',
        'tanggal_desk_evaluation',
        'jadwal_visitasi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'audit_period_id' => 'integer',
            'unit_id' => 'integer',
            'lead_auditor_id' => 'integer',
            'tanggal_desk_evaluation' => 'date',
            'jadwal_visitasi' => 'date',
        ];
    }

    public function auditPeriod(): BelongsTo
    {
        return $this->belongsTo(AuditPeriod::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function auditors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignment_auditors', 'assignment_id', 'auditor_id')
            ->withPivot('peran_dalam_tim')
            ->withTimestamps();
    }

    public function selfAssessments(): HasMany
    {
        return $this->hasMany(SelfAssessment::class, 'assignment_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'assignment_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'assignment_id');
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class, 'assignment_id');
    }

    public function progressEvaluasiDiri(): int
    {
        $total = $this->selfAssessments()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->selfAssessments()
            ->whereIn('status', ['dikirim', 'final'])
            ->count();

        return (int) round(($completed / $total) * 100);
    }

    public function deskEvaluationStatus(): string
    {
        if ($this->evaluations()->exists() && $this->evaluations()->where('status_pemeriksaan', '!=', 'final')->doesntExist()) {
            return 'Final';
        }

        return $this->tanggal_desk_evaluation ? 'Terjadwal' : 'Belum dijadwalkan';
    }

    public function visitasiStatus(): string
    {
        return $this->visit?->status
            ? (Visit::statusOptions()[$this->visit->status] ?? $this->visit->status)
            : ($this->jadwal_visitasi ? 'Terjadwal' : 'Belum dijadwalkan');
    }

    public function activeFindingsCount(): int
    {
        return $this->findings()
            ->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat'])
            ->count();
    }

    public function followUpStatus(): string
    {
        return 'Belum ada tindak lanjut';
    }

    public function hasConflictOfInterest(): bool
    {
        $auditorIds = collect([$this->lead_auditor_id])
            ->merge($this->auditors->pluck('id'))
            ->filter()
            ->unique();

        return User::query()
            ->whereIn('id', $auditorIds)
            ->whereNotNull('unit_id')
            ->where('unit_id', $this->unit_id)
            ->exists();
    }

    public static function statusOptions(): array
    {
        return [
            'aktif' => 'Aktif',
            'dibatalkan' => 'Dibatalkan',
        ];
    }
}

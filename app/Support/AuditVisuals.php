<?php

namespace App\Support;

use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Finding;
use App\Models\SelfAssessment;
use App\Models\Standard;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AuditVisuals
{
    public static function statusScore(?string $status): int
    {
        return match ($status) {
            'final' => 100,
            'dikirim' => 82,
            'perlu_klarifikasi' => 58,
            'draft' => 36,
            default => 0,
        };
    }

    public static function toneForScore(int $score): string
    {
        return match (true) {
            $score >= 80 => 'success',
            $score >= 50 => 'warning',
            default => 'danger',
        };
    }

    public static function readiness(Collection $assessments, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        $score = $assessments->sum(fn (SelfAssessment $assessment): int => self::statusScore($assessment->status));

        return (int) round($score / $total);
    }

    public static function standardScores(Collection $standards, Collection $assessments): array
    {
        $byStandard = $assessments
            ->filter(fn (SelfAssessment $assessment): bool => (bool) $assessment->instrument?->standard_id)
            ->groupBy(fn (SelfAssessment $assessment): int => $assessment->instrument->standard_id);

        return $standards->map(function (Standard $standard) use ($byStandard): array {
            $items = $byStandard->get($standard->id, collect());
            $score = $items->isEmpty()
                ? 0
                : (int) round($items->avg(fn (SelfAssessment $assessment): int => self::statusScore($assessment->status)));

            return [
                'id' => $standard->id,
                'label' => $standard->kode,
                'title' => $standard->nama,
                'value' => $score,
                'target' => 100,
                'tone' => self::toneForScore($score),
            ];
        })->values()->all();
    }

    public static function heatmap(Collection $assignments, Collection $standards): array
    {
        return $assignments->map(function (AuditAssignment $assignment) use ($standards): array {
            $assignment->loadMissing(['unit', 'selfAssessments.instrument.standard']);

            return [
                'label' => $assignment->unit->kode,
                'title' => $assignment->unit->nama,
                'url' => route('admin.assignments.show', $assignment, absolute: false),
                'values' => self::standardScores($standards, $assignment->selfAssessments),
            ];
        })->values()->all();
    }

    public static function averageStandards(Collection $assignments, Collection $standards): array
    {
        return $standards->map(function (Standard $standard) use ($assignments): array {
            $scores = $assignments->map(function (AuditAssignment $assignment) use ($standard): int {
                $assignment->loadMissing('selfAssessments.instrument.standard');
                $assessments = $assignment->selfAssessments
                    ->filter(fn (SelfAssessment $assessment): bool => $assessment->instrument?->standard_id === $standard->id);

                if ($assessments->isEmpty()) {
                    return 0;
                }

                return (int) round($assessments->avg(fn (SelfAssessment $assessment): int => self::statusScore($assessment->status)));
            });

            $score = $scores->isEmpty() ? 0 : (int) round($scores->avg());

            return [
                'id' => $standard->id,
                'label' => $standard->kode,
                'title' => $standard->nama,
                'value' => $score,
                'target' => 100,
                'tone' => self::toneForScore($score),
            ];
        })->values()->all();
    }

    public static function assignmentTimeline(Collection $assignments): array
    {
        return $assignments->map(function (AuditAssignment $assignment): array {
            $assignment->loadMissing(['unit', 'auditPeriod', 'selfAssessments', 'evaluations', 'visit', 'findings.followUps']);

            return [
                'label' => $assignment->unit->kode,
                'title' => $assignment->unit->nama,
                'url' => route('admin.assignments.show', $assignment, absolute: false),
                'late' => self::assignmentHasLateWork($assignment),
                'segments' => [
                    self::segment('Evaluasi Diri', self::selfEvaluationPercent($assignment), $assignment->auditPeriod?->batas_evaluasi_diri),
                    self::segment('Desk Evaluation', self::deskEvaluationPercent($assignment), $assignment->auditPeriod?->batas_desk_evaluation),
                    self::segment('Visitasi', self::visitPercent($assignment), $assignment->auditPeriod?->visitasi_selesai),
                    self::segment('Tindak Lanjut', self::followUpPercent($assignment), $assignment->auditPeriod?->batas_tindak_lanjut),
                ],
            ];
        })->values()->all();
    }

    public static function periodMarkers(?AuditPeriod $period): array
    {
        if (! $period) {
            return [];
        }

        return [
            ['label' => 'Mulai', 'date' => $period->tanggal_mulai],
            ['label' => 'Evaluasi Diri', 'date' => $period->batas_evaluasi_diri],
            ['label' => 'Desk', 'date' => $period->batas_desk_evaluation],
            ['label' => 'Visitasi', 'date' => $period->visitasi_selesai ?? $period->visitasi_mulai],
            ['label' => 'Tindak Lanjut', 'date' => $period->batas_tindak_lanjut],
        ];
    }

    public static function deadlineMeta(CarbonInterface $date): array
    {
        $days = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

        return [
            'days' => (int) $days,
            'label' => match (true) {
                $days < 0 => 'Terlambat '.abs((int) $days).' hari',
                $days === 0 => 'Hari ini',
                $days <= 3 => 'H-'.$days,
                default => $days.' hari lagi',
            },
            'tone' => match (true) {
                $days < 0 => 'danger',
                $days <= 3 => 'warning',
                default => 'neutral',
            },
        ];
    }

    private static function segment(string $label, int $percent, ?CarbonInterface $deadline): array
    {
        return [
            'label' => $label,
            'percent' => $percent,
            'tone' => self::toneForScore($percent),
            'deadline' => $deadline?->format('d/m/Y'),
        ];
    }

    private static function selfEvaluationPercent(AuditAssignment $assignment): int
    {
        $total = max($assignment->selfAssessments->count(), 1);
        $score = $assignment->selfAssessments->sum(fn (SelfAssessment $assessment): int => self::statusScore($assessment->status));

        return (int) round($score / $total);
    }

    private static function deskEvaluationPercent(AuditAssignment $assignment): int
    {
        $total = max($assignment->evaluations->count(), 1);
        $checked = $assignment->evaluations->where('status_pemeriksaan', '!=', 'belum_dimulai')->count();
        $final = $assignment->evaluations->where('status_pemeriksaan', 'final')->count();

        return $final === $total ? 100 : (int) round(($checked / $total) * 75);
    }

    private static function visitPercent(AuditAssignment $assignment): int
    {
        return match ($assignment->visit?->status) {
            'berita_acara_disetujui' => 100,
            'selesai' => 82,
            'terjadwal' => 45,
            default => $assignment->jadwal_visitasi ? 35 : 0,
        };
    }

    private static function followUpPercent(AuditAssignment $assignment): int
    {
        $findings = $assignment->findings;

        if ($findings->isEmpty()) {
            return 100;
        }

        $closed = $findings->where('status', 'ditutup')->count();
        $waiting = $findings->where('status', 'menunggu_verifikasi')->count();
        $late = $findings->where('status', 'terlambat')->count();

        return (int) round((($closed * 100) + ($waiting * 72) + (($findings->count() - $closed - $waiting - $late) * 45)) / max($findings->count(), 1));
    }

    private static function assignmentHasLateWork(AuditAssignment $assignment): bool
    {
        $selfLate = $assignment->auditPeriod
            && $assignment->auditPeriod->batas_evaluasi_diri->toDateString() < now()->toDateString()
            && $assignment->selfAssessments->where('status', 'final')->count() < $assignment->selfAssessments->count();

        return $selfLate || $assignment->findings->contains(fn (Finding $finding): bool => $finding->status === 'terlambat');
    }
}

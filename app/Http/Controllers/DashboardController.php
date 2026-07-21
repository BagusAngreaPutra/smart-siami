<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Clarification;
use App\Models\Evaluation;
use App\Models\Finding;
use App\Models\FollowUp;
use App\Models\Instrument;
use App\Models\SelfAssessment;
use App\Models\Standard;
use App\Models\SystemLog;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visit;
use App\Support\AuditVisuals;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(Request $request): View
    {
        $activePeriod = AuditPeriod::query()->where('status', 'aktif')->first();
        $selectedPeriod = AuditPeriod::query()
            ->whereKey($request->integer('audit_period_id') ?: $activePeriod?->id)
            ->first() ?? $activePeriod;

        $periodId = $selectedPeriod?->id;
        $assignmentScope = fn (Builder $query) => $periodId ? $query->where('audit_period_id', $periodId) : $query;
        $standards = Standard::query()->where('is_active', true)->orderBy('urutan')->get();
        $visualAssignments = AuditAssignment::query()
            ->with(['unit', 'auditPeriod', 'selfAssessments.instrument.standard', 'evaluations', 'visit', 'findings.followUps'])
            ->where('status', 'aktif')
            ->when($periodId, fn ($query) => $query->where('audit_period_id', $periodId))
            ->orderBy('unit_id')
            ->get();

        $selfEvaluationProgress = $this->selfEvaluationProgress($periodId);
        $lateFollowUps = FollowUp::query()
            ->with(['finding.assignment.unit'])
            ->whereIn('status', ['draft', 'diajukan', 'perlu_perbaikan'])
            ->whereDate('target_penyelesaian', '<', now()->toDateString())
            ->whereHas('assignment', $assignmentScope)
            ->orderBy('target_penyelesaian')
            ->limit(5)
            ->get();

        return view('dashboards.admin', [
            'periods' => AuditPeriod::query()->orderByDesc('tanggal_mulai')->get(),
            'selectedPeriod' => $selectedPeriod,
            'cards' => [
                [
                    'label' => 'Total Unit',
                    'value' => Unit::query()->count(),
                    'url' => route('admin.users', ['tab' => 'units']),
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Total Auditor',
                    'value' => User::query()->where('role', UserRole::Auditor->value)->count(),
                    'url' => route('admin.users', ['tab' => 'users', 'user_role' => 'auditor']),
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Total Penugasan Aktif',
                    'value' => AuditAssignment::query()->where('status', 'aktif')->when($periodId, fn ($query) => $query->where('audit_period_id', $periodId))->count(),
                    'url' => route('admin.assignments', array_filter(['audit_period_id' => $periodId, 'status' => 'aktif'])),
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Total Temuan Aktif',
                    'value' => Finding::query()->whereHas('assignment', $assignmentScope)->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat'])->count(),
                    'url' => route('admin.assignments', array_filter(['audit_period_id' => $periodId])),
                    'tone' => 'danger',
                ],
                [
                    'label' => 'Tindak Lanjut Terlambat',
                    'value' => FollowUp::query()->whereHas('assignment', $assignmentScope)->whereIn('status', ['draft', 'diajukan', 'perlu_perbaikan'])->whereDate('target_penyelesaian', '<', now()->toDateString())->count(),
                    'url' => '#late-follow-ups',
                    'tone' => 'danger',
                ],
            ],
            'selfEvaluationProgress' => $selfEvaluationProgress,
            'institutionReadiness' => $visualAssignments->isEmpty()
                ? 0
                : (int) round($visualAssignments->avg(fn (AuditAssignment $assignment): int => AuditVisuals::readiness($assignment->selfAssessments, max($assignment->selfAssessments->count(), 1)))),
            'radarScores' => AuditVisuals::averageStandards($visualAssignments, $standards),
            'heatmapStandards' => $standards,
            'heatmapRows' => AuditVisuals::heatmap($visualAssignments, $standards),
            'lateFollowUps' => $lateFollowUps,
            'upcomingVisits' => Visit::query()
                ->with(['assignment.unit', 'assignment.auditPeriod'])
                ->whereHas('assignment', $assignmentScope)
                ->whereDate('tanggal', '>=', now()->toDateString())
                ->orderBy('tanggal')
                ->limit(5)
                ->get(),
            'activities' => SystemLog::query()->latest('created_at')->latest('id')->limit(10)->get(),
        ]);
    }

    public function auditor(Request $request): View
    {
        $assignments = $this->auditorAssignments($request)
            ->with(['auditPeriod', 'unit', 'evaluations', 'visit'])
            ->get();
        $assignmentIds = $assignments->pluck('id');
        $activeInstrumentCount = Instrument::query()->where('is_active', true)->count();
        $checkedEvaluations = Evaluation::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->where('status_pemeriksaan', '!=', 'belum_dimulai')
            ->count();

        $deadlineWarnings = $assignments
            ->filter(fn (AuditAssignment $assignment): bool => $assignment->auditPeriod->batas_desk_evaluation->betweenIncluded(now()->startOfDay(), now()->addDays(3)->endOfDay()))
            ->values();

        return view('dashboards.auditor', [
            'cards' => [
                [
                    'label' => 'Tugas Aktif',
                    'value' => $assignments->count(),
                    'url' => route('auditor.tasks'),
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Instrumen Belum Diperiksa',
                    'value' => max(($assignments->count() * $activeInstrumentCount) - $checkedEvaluations, 0),
                    'url' => route('auditor.desk-evaluation'),
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Klarifikasi Menunggu Tanggapan Auditee',
                    'value' => Clarification::query()->whereIn('assignment_id', $assignmentIds)->whereIn('status', ['terbuka', 'dibuka_kembali'])->count(),
                    'url' => route('auditor.clarifications', ['status' => 'terbuka']),
                    'tone' => 'danger',
                ],
                [
                    'label' => 'Tindak Lanjut Menunggu Verifikasi',
                    'value' => FollowUp::query()->whereIn('assignment_id', $assignmentIds)->where('status', 'diajukan')->count(),
                    'url' => route('auditor.follow-up-verifications'),
                    'tone' => 'danger',
                ],
            ],
            'assignments' => $assignments->map(function (AuditAssignment $assignment) use ($activeInstrumentCount): AuditAssignment {
                $checked = $assignment->evaluations->where('status_pemeriksaan', '!=', 'belum_dimulai')->count();
                $assignment->desk_progress = $activeInstrumentCount > 0 ? (int) round(($checked / $activeInstrumentCount) * 100) : 0;
                $assignment->desk_checked = $checked;
                $assignment->desk_total = $activeInstrumentCount;

                return $assignment;
            }),
            'upcomingVisits' => Visit::query()
                ->with(['assignment.unit', 'assignment.auditPeriod'])
                ->whereIn('assignment_id', $assignmentIds)
                ->whereDate('tanggal', '>=', now()->toDateString())
                ->orderBy('tanggal')
                ->limit(3)
                ->get(),
            'draftFindings' => Finding::query()
                ->with(['assignment.unit', 'standard'])
                ->whereIn('assignment_id', $assignmentIds)
                ->where('status', 'draft')
                ->latest('id')
                ->limit(5)
                ->get(),
            'deadlineWarnings' => $deadlineWarnings,
        ]);
    }

    public function auditee(Request $request): View
    {
        $assignment = AuditAssignment::query()
            ->with(['auditPeriod', 'unit'])
            ->where('unit_id', $request->user()->unit_id)
            ->where('status', 'aktif')
            ->whereHas('auditPeriod', fn ($query) => $query->where('status', 'aktif'))
            ->latest('id')
            ->first();

        $activeInstrumentCount = Instrument::query()->where('is_active', true)->count();
        $standards = Standard::query()->where('is_active', true)->orderBy('urutan')->get();
        $assessments = $assignment
            ? SelfAssessment::query()->with('instrument.standard')->where('assignment_id', $assignment->id)->get()
            : collect();
        $submittedOrFinal = $assessments->whereIn('status', ['dikirim', 'final'])->count();
        $remainingDays = $assignment ? now()->startOfDay()->diffInDays($assignment->auditPeriod->batas_evaluasi_diri->startOfDay(), false) : null;

        return view('dashboards.auditee', [
            'assignment' => $assignment,
            'cards' => [
                [
                    'label' => 'Total Instrumen',
                    'value' => $activeInstrumentCount,
                    'url' => route('auditee.self-evaluations'),
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Belum Diisi',
                    'value' => max($activeInstrumentCount - $assessments->where('status', '!=', 'belum_diisi')->count(), 0),
                    'url' => route('auditee.self-evaluations'),
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Perlu Klarifikasi',
                    'value' => $assessments->where('status', 'perlu_klarifikasi')->count(),
                    'url' => route('auditee.clarifications'),
                    'tone' => 'danger',
                ],
                [
                    'label' => 'Sudah Final',
                    'value' => $assessments->where('status', 'final')->count(),
                    'url' => route('auditee.self-evaluations'),
                    'tone' => 'success',
                ],
            ],
            'readinessProgress' => $activeInstrumentCount > 0 ? (int) round(($submittedOrFinal / $activeInstrumentCount) * 100) : 0,
            'readinessGauge' => AuditVisuals::readiness($assessments, max($activeInstrumentCount, 1)),
            'standardScores' => AuditVisuals::standardScores($standards, $assessments),
            'nextVisit' => Visit::query()
                ->with(['assignment.auditPeriod'])
                ->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->user()->unit_id))
                ->whereDate('tanggal', '>=', now()->toDateString())
                ->orderBy('tanggal')
                ->first(),
            'activeFindings' => Finding::query()
                ->with(['assignment.auditPeriod', 'latestFollowUp'])
                ->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->user()->unit_id))
                ->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat'])
                ->latest('id')
                ->limit(5)
                ->get(),
            'urgentFollowUps' => FollowUp::query()
                ->with(['finding'])
                ->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->user()->unit_id))
                ->whereIn('status', ['draft', 'diajukan', 'perlu_perbaikan'])
                ->whereDate('target_penyelesaian', '<=', now()->addDays(7)->toDateString())
                ->orderBy('target_penyelesaian')
                ->limit(5)
                ->get(),
            'remainingDays' => $remainingDays,
        ]);
    }

    private function auditorAssignments(Request $request)
    {
        return AuditAssignment::query()
            ->where('status', 'aktif')
            ->whereHas('auditPeriod', fn ($query) => $query->where('status', 'aktif'))
            ->where(function ($query) use ($request): void {
                $query->where('lead_auditor_id', $request->user()->id)
                    ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $request->user()->id));
            })
            ->latest('id');
    }

    /**
     * @return array{final: int, draft: int, not_started: int, total: int}
     */
    private function selfEvaluationProgress(?int $periodId): array
    {
        if (! $periodId) {
            return ['final' => 0, 'draft' => 0, 'not_started' => 0, 'total' => 0];
        }

        $assignments = AuditAssignment::query()
            ->where('audit_period_id', $periodId)
            ->withCount([
                'selfAssessments',
                'selfAssessments as final_assessments_count' => fn ($query) => $query->where('status', 'final'),
                'selfAssessments as started_assessments_count' => fn ($query) => $query->where('status', '!=', 'belum_diisi'),
            ])
            ->get();

        return [
            'final' => $assignments->filter(fn (AuditAssignment $assignment): bool => $assignment->self_assessments_count > 0 && $assignment->self_assessments_count === $assignment->final_assessments_count)->count(),
            'draft' => $assignments->filter(fn (AuditAssignment $assignment): bool => $assignment->started_assessments_count > 0 && $assignment->self_assessments_count !== $assignment->final_assessments_count)->count(),
            'not_started' => $assignments->filter(fn (AuditAssignment $assignment): bool => $assignment->started_assessments_count === 0)->count(),
            'total' => $assignments->count(),
        ];
    }

}

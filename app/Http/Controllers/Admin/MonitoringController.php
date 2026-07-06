<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Finding;
use App\Models\FollowUp;
use App\Models\Notification;
use App\Models\Standard;
use App\Models\Unit;
use App\Models\User;
use App\Support\ExcelXml;
use App\Support\AuditVisuals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $activePeriod = AuditPeriod::query()->where('status', 'aktif')->first();
        $selectedPeriodId = $request->integer('audit_period_id') ?: $activePeriod?->id;

        $assignments = $this->assignmentQuery($request, $selectedPeriodId)
            ->with([
                'auditPeriod',
                'unit',
                'leadAuditor',
                'auditors',
                'selfAssessments',
                'evaluations',
                'visit',
                'findings.latestFollowUp',
                'findings.followUps',
            ])
            ->orderBy('unit_id')
            ->get();

        $progressRows = $assignments
            ->map(fn (AuditAssignment $assignment): array => $this->progressRow($assignment, $request->integer('standard_id') ?: null))
            ->values();
        $standards = Standard::query()->where('is_active', true)->orderBy('urutan')->get();
        $selectedPeriod = $selectedPeriodId ? AuditPeriod::query()->find($selectedPeriodId) : null;

        return view('admin.monitoring.index', [
            'activeTab' => $request->query('tab', 'progress'),
            'periodOptions' => AuditPeriod::query()->orderByDesc('tanggal_mulai')->get(),
            'unitOptions' => Unit::query()->orderBy('kode')->get(),
            'auditorOptions' => User::query()->where('role', UserRole::Auditor->value)->where('is_active', true)->orderBy('name')->get(),
            'standardOptions' => $standards,
            'selectedPeriodId' => $selectedPeriodId,
            'progressRows' => $progressRows,
            'heatmapRows' => AuditVisuals::heatmap($assignments, $standards),
            'heatmapStandards' => $standards,
            'timelineRows' => AuditVisuals::assignmentTimeline($assignments),
            'timelineMarkers' => AuditVisuals::periodMarkers($selectedPeriod),
            'unfinishedSelfEvaluations' => $progressRows->where('self_evaluation_overdue', true)->values(),
            'lateFindings' => $this->lateFindingsQuery($request, $selectedPeriodId)->get(),
            'pendingFollowUps' => $this->pendingFollowUpsQuery($request, $selectedPeriodId)->get(),
        ]);
    }

    public function sendReminder(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $assignment->loadMissing('unit');
        $process = $request->string('process')->toString() ?: $this->progressRow($assignment)['pending_process'];
        $count = $this->sendAssignmentReminder($assignment, $process);

        return back()->with('status', "Pengingat dikirim ke {$count} auditee {$assignment->unit->kode}.");
    }

    public function sendSelfEvaluationReminders(Request $request): RedirectResponse
    {
        $activePeriod = AuditPeriod::query()->where('status', 'aktif')->first();
        $selectedPeriodId = $request->integer('audit_period_id') ?: $activePeriod?->id;
        $assignments = $this->assignmentQuery($request, $selectedPeriodId)
            ->with(['auditPeriod', 'unit', 'selfAssessments'])
            ->get();

        $count = $assignments
            ->filter(fn (AuditAssignment $assignment): bool => $this->progressRow($assignment)['self_evaluation_overdue'])
            ->sum(fn (AuditAssignment $assignment): int => $this->sendAssignmentReminder($assignment, 'evaluasi diri'));

        return back()->with('status', "Pengingat massal evaluasi diri dikirim ke {$count} auditee.");
    }

    public function exportProgress(Request $request): StreamedResponse
    {
        $activePeriod = AuditPeriod::query()->where('status', 'aktif')->first();
        $selectedPeriodId = $request->integer('audit_period_id') ?: $activePeriod?->id;
        $rows = $this->assignmentQuery($request, $selectedPeriodId)
            ->with(['auditPeriod', 'unit', 'leadAuditor', 'selfAssessments', 'evaluations', 'visit', 'findings.latestFollowUp', 'findings.followUps'])
            ->get()
            ->map(fn (AuditAssignment $assignment): array => $this->progressRow($assignment, $request->integer('standard_id') ?: null))
            ->map(fn (array $row): array => [
                $row['unit'],
                $row['lead_auditor'],
                $row['self_evaluation_status'],
                $row['desk_evaluation_status'],
                $row['visit_schedule'],
                $row['findings_count'],
                $row['follow_up_status'],
            ])
            ->all();

        return ExcelXml::download('monitoring-progres-unit.xls', 'Progres Unit', [
            'Unit',
            'Lead Auditor',
            'Status Evaluasi Diri',
            'Status Desk Evaluation',
            'Jadwal Visitasi',
            'Jumlah Temuan',
            'Status Tindak Lanjut',
        ], $rows);
    }

    public function exportLateFindings(Request $request): StreamedResponse
    {
        $activePeriod = AuditPeriod::query()->where('status', 'aktif')->first();
        $selectedPeriodId = $request->integer('audit_period_id') ?: $activePeriod?->id;
        $rows = $this->lateFindingsQuery($request, $selectedPeriodId)
            ->get()
            ->map(fn (Finding $finding): array => [
                $finding->nomor_temuan,
                $finding->assignment->unit->kode.' - '.$finding->assignment->unit->nama,
                Finding::kategoriOptions()[$finding->kategori] ?? $finding->kategori,
                Finding::prioritasOptions()[$finding->prioritas] ?? $finding->prioritas,
                $finding->target_penyelesaian->format('d/m/Y'),
                $finding->target_penyelesaian->diffInDays(now()),
            ])
            ->all();

        return ExcelXml::download('monitoring-temuan-terlambat.xls', 'Temuan Terlambat', [
            'Nomor Temuan',
            'Unit',
            'Kategori',
            'Prioritas',
            'Target Awal',
            'Hari Terlambat',
        ], $rows);
    }

    private function assignmentQuery(Request $request, ?int $periodId)
    {
        return AuditAssignment::query()
            ->where('status', 'aktif')
            ->when($periodId, fn ($query) => $query->where('audit_period_id', $periodId))
            ->when($request->filled('unit_id'), fn ($query) => $query->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('auditor_id'), function ($query) use ($request): void {
                $auditorId = $request->integer('auditor_id');
                $query->where(function ($query) use ($auditorId): void {
                    $query->where('lead_auditor_id', $auditorId)
                        ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $auditorId));
                });
            });
    }

    private function lateFindingsQuery(Request $request, ?int $periodId)
    {
        return Finding::query()
            ->with(['assignment.unit', 'assignment.leadAuditor', 'standard'])
            ->where('status', 'terlambat')
            ->when($periodId, fn ($query) => $query->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $periodId)))
            ->when($request->filled('unit_id'), fn ($query) => $query->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->integer('unit_id'))))
            ->when($request->filled('auditor_id'), function ($query) use ($request): void {
                $auditorId = $request->integer('auditor_id');
                $query->whereHas('assignment', function ($query) use ($auditorId): void {
                    $query->where('lead_auditor_id', $auditorId)
                        ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $auditorId));
                });
            })
            ->when($request->filled('standard_id'), fn ($query) => $query->where('standard_id', $request->integer('standard_id')))
            ->oldest('target_penyelesaian');
    }

    private function pendingFollowUpsQuery(Request $request, ?int $periodId)
    {
        return FollowUp::query()
            ->with(['finding', 'assignment.unit', 'assignment.leadAuditor'])
            ->where('status', 'diajukan')
            ->when($periodId, fn ($query) => $query->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $periodId)))
            ->when($request->filled('unit_id'), fn ($query) => $query->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->integer('unit_id'))))
            ->when($request->filled('auditor_id'), function ($query) use ($request): void {
                $auditorId = $request->integer('auditor_id');
                $query->whereHas('assignment', function ($query) use ($auditorId): void {
                    $query->where('lead_auditor_id', $auditorId)
                        ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $auditorId));
                });
            })
            ->oldest('updated_at');
    }

    private function progressRow(AuditAssignment $assignment, ?int $standardId = null): array
    {
        $assignment->loadMissing(['auditPeriod', 'unit', 'leadAuditor', 'selfAssessments', 'evaluations', 'visit', 'findings.latestFollowUp', 'findings.followUps']);

        $selfStatuses = $assignment->selfAssessments->pluck('status');
        $selfFinal = $selfStatuses->isNotEmpty() && $selfStatuses->every(fn (string $status): bool => $status === 'final');
        $selfStarted = $selfStatuses->contains(fn (string $status): bool => $status !== 'belum_diisi');
        $selfOverdue = ! $selfFinal
            && $assignment->auditPeriod
            && $assignment->auditPeriod->batas_evaluasi_diri->toDateString() < now()->toDateString();
        $selfStatus = match (true) {
            $selfFinal => 'Final',
            $selfOverdue => 'Terlambat',
            $selfStarted => 'Draft/Berjalan',
            default => 'Belum Mulai',
        };

        $deskStatuses = $assignment->evaluations->pluck('status_pemeriksaan');
        $deskStatus = match (true) {
            $deskStatuses->isNotEmpty() && $deskStatuses->every(fn (string $status): bool => $status === 'final') => 'Final',
            $deskStatuses->contains(fn (string $status): bool => $status !== 'belum_dimulai') => 'Berlangsung',
            default => 'Belum Dimulai',
        };

        $findings = $assignment->findings;
        if ($standardId) {
            $findings = $findings->where('standard_id', $standardId);
        }

        $followUpStatus = $this->followUpStatus($findings);
        $hasLateFinding = $findings->contains(fn (Finding $finding): bool => $finding->status === 'terlambat'
            || ($finding->target_penyelesaian->toDateString() < now()->toDateString() && $finding->status !== 'ditutup'));
        $hasPendingFollowUp = $findings->flatMap(fn (Finding $finding): Collection => $finding->followUps)
            ->contains(fn (FollowUp $followUp): bool => in_array($followUp->status, ['diajukan', 'perlu_perbaikan'], true));
        $pendingProcess = $selfOverdue ? 'evaluasi diri' : ($hasPendingFollowUp || $hasLateFinding ? 'tindak lanjut temuan' : 'proses audit');

        return [
            'assignment' => $assignment,
            'unit' => $assignment->unit->kode.' - '.$assignment->unit->nama,
            'lead_auditor' => $assignment->leadAuditor->name,
            'self_evaluation_status' => $selfStatus,
            'self_evaluation_badge' => $selfFinal ? 'success' : ($selfOverdue ? 'danger' : ($selfStarted ? 'warning' : 'neutral')),
            'self_evaluation_overdue' => $selfOverdue,
            'desk_evaluation_status' => $deskStatus,
            'desk_evaluation_badge' => $deskStatus === 'Final' ? 'success' : ($deskStatus === 'Berlangsung' ? 'warning' : 'neutral'),
            'visit_schedule' => $assignment->visit?->tanggal?->format('d/m/Y') ?? $assignment->jadwal_visitasi?->format('d/m/Y') ?? 'Belum dijadwalkan',
            'findings_count' => $findings->count(),
            'follow_up_status' => $followUpStatus,
            'follow_up_badge' => str_contains($followUpStatus, 'Menunggu') ? 'warning' : (str_contains($followUpStatus, 'Selesai') ? 'success' : 'neutral'),
            'pending_process' => $pendingProcess,
            'is_late' => $selfOverdue || $hasLateFinding,
        ];
    }

    private function followUpStatus(Collection $findings): string
    {
        if ($findings->isEmpty()) {
            return 'Belum Ada Temuan';
        }

        $followUps = $findings->flatMap(fn (Finding $finding): Collection => $finding->followUps);

        if ($followUps->contains(fn (FollowUp $followUp): bool => $followUp->status === 'diajukan')) {
            return 'Menunggu Verifikasi';
        }

        if ($followUps->contains(fn (FollowUp $followUp): bool => $followUp->status === 'perlu_perbaikan')) {
            return 'Perlu Perbaikan';
        }

        if ($followUps->isNotEmpty() && $followUps->every(fn (FollowUp $followUp): bool => $followUp->status === 'disetujui')) {
            return 'Selesai';
        }

        return 'Belum Ada Tindak Lanjut';
    }

    private function sendAssignmentReminder(AuditAssignment $assignment, string $process): int
    {
        $auditees = User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('is_active', true)
            ->where('unit_id', $assignment->unit_id)
            ->get();

        $auditees->each(fn (User $user): Notification => Notification::sendNotification(
            $user->id,
            'pengingat_manual',
            'Pengingat dari Admin',
            "Pengingat dari Admin: mohon segera melengkapi {$process}.",
            route('auditee.dashboard', absolute: false),
            'audit_assignment',
            $assignment->id,
        ));

        return $auditees->count();
    }
}

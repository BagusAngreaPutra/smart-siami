<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Notification;
use App\Models\Unit;
use App\Models\User;
use App\Support\SimplePdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AuditAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $activePeriod = AuditPeriod::query()->where('status', 'aktif')->first();
        $periodFilter = $request->query('audit_period_id', $activePeriod?->id);

        $query = AuditAssignment::query()
            ->with(['auditPeriod', 'unit', 'leadAuditor', 'auditors'])
            ->latest('id');

        if ($periodFilter) {
            $query->where('audit_period_id', $periodFilter);
        }

        if ($request->filled('auditor_id')) {
            $auditorId = $request->integer('auditor_id');
            $query->where(function ($query) use ($auditorId): void {
                $query->where('lead_auditor_id', $auditorId)
                    ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $auditorId));
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->integer('unit_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('admin.audit-assignments.index', [
            'assignments' => $query->paginate(10)->withQueryString(),
            'periodOptions' => AuditPeriod::query()->orderByDesc('tanggal_mulai')->get(),
            'activePeriod' => $activePeriod,
            'selectedPeriodId' => $periodFilter,
            'auditorOptions' => $this->auditorQuery()->orderBy('name')->get(),
            'unitOptions' => Unit::query()->where('is_active', true)->orderBy('kode')->get(),
            'statusOptions' => AuditAssignment::statusOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        $activePeriod = AuditPeriod::query()->where('status', 'aktif')->first();

        return view('admin.audit-assignments.form', [
            'assignment' => new AuditAssignment([
                'audit_period_id' => $request->integer('audit_period_id') ?: $activePeriod?->id,
                'status' => 'aktif',
            ]),
            'periodOptions' => AuditPeriod::query()->whereIn('status', ['aktif', 'draft'])->orderByDesc('tanggal_mulai')->get(),
            'unitOptions' => Unit::query()->where('is_active', true)->orderBy('kode')->get(),
            'auditorOptions' => $this->auditorQuery()->orderBy('name')->get(),
            'selectedMemberIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->createRules());
        $this->validateSingleActiveAssignment($validated['audit_period_id'], $validated['unit_id']);

        $assignment = DB::transaction(function () use ($validated): AuditAssignment {
            $assignment = AuditAssignment::query()->create(Arr::only($validated, [
                'audit_period_id',
                'unit_id',
                'lead_auditor_id',
                'catatan_penugasan',
                'tanggal_desk_evaluation',
                'jadwal_visitasi',
            ]) + ['status' => 'aktif']);
            $this->syncAuditors($assignment, $validated['lead_auditor_id'], $validated['member_auditor_ids'] ?? []);

            return $assignment->load(['auditPeriod', 'unit', 'leadAuditor', 'auditors']);
        });

        $this->notifyAssignmentUsers($assignment);

        $redirect = redirect()->route('admin.assignments.show', $assignment)
            ->with('status', 'Penugasan audit berhasil dibuat dan notifikasi telah dikirim.');

        return $this->withConflictWarning($redirect, $assignment);
    }

    public function show(AuditAssignment $assignment): View
    {
        return view('admin.audit-assignments.show', [
            'assignment' => $assignment->load(['auditPeriod', 'unit', 'leadAuditor', 'auditors']),
            'summary' => $this->summary($assignment),
        ]);
    }

    public function edit(AuditAssignment $assignment): View
    {
        return view('admin.audit-assignments.form', [
            'assignment' => $assignment->load('auditors'),
            'periodOptions' => AuditPeriod::query()->whereKey($assignment->audit_period_id)->get(),
            'unitOptions' => Unit::query()->whereKey($assignment->unit_id)->get(),
            'auditorOptions' => $this->auditorQuery()->orderBy('name')->get(),
            'selectedMemberIds' => $assignment->auditors()
                ->wherePivot('peran_dalam_tim', 'anggota')
                ->pluck('users.id')
                ->all(),
        ]);
    }

    public function update(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        if ($assignment->status === 'dibatalkan') {
            return redirect()->route('admin.assignments.show', $assignment)->with('warning', 'Penugasan yang dibatalkan tidak dapat diubah.');
        }

        $validated = $request->validate($this->updateRules());

        DB::transaction(function () use ($assignment, $validated): void {
            $assignment->update(Arr::only($validated, [
                'lead_auditor_id',
                'catatan_penugasan',
                'tanggal_desk_evaluation',
                'jadwal_visitasi',
            ]));
            $this->syncAuditors($assignment, $validated['lead_auditor_id'], $validated['member_auditor_ids'] ?? []);
        });

        $assignment->refresh()->load(['auditPeriod', 'unit', 'leadAuditor', 'auditors']);
        $this->notifyAssignmentUsers($assignment);

        $redirect = redirect()->route('admin.assignments.show', $assignment)
            ->with('status', 'Auditor dan jadwal penugasan berhasil diperbarui. Notifikasi telah dikirim.');

        return $this->withConflictWarning($redirect, $assignment);
    }

    public function cancel(AuditAssignment $assignment): RedirectResponse
    {
        if ($assignment->status === 'dibatalkan') {
            return back()->with('warning', 'Penugasan sudah berstatus dibatalkan.');
        }

        $assignment->update(['status' => 'dibatalkan']);

        return back()->with('status', 'Penugasan audit berhasil dibatalkan tanpa menghapus data audit.');
    }

    public function destroy(AuditAssignment $assignment): RedirectResponse
    {
        if ($this->hasAuditData($assignment)) {
            return back()->with('warning', 'Penugasan tidak dapat dihapus karena sudah memiliki data audit. Gunakan Batalkan agar riwayat tetap aman.');
        }

        DB::transaction(function () use ($assignment): void {
            $assignment->auditors()->detach();
            $assignment->delete();
        });

        return back()->with('status', 'Penugasan audit berhasil dihapus.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['cancel', 'delete'])],
            'assignment_ids' => ['required', 'array', 'min:1'],
            'assignment_ids.*' => ['integer', 'exists:audit_assignments,id'],
        ]);

        if ($validated['action'] === 'cancel') {
            $updated = AuditAssignment::query()
                ->whereIn('id', $validated['assignment_ids'])
                ->where('status', 'aktif')
                ->update(['status' => 'dibatalkan']);

            return back()->with('status', "{$updated} penugasan berhasil dibatalkan.");
        }

        $deleted = 0;
        $blocked = 0;

        AuditAssignment::query()
            ->whereIn('id', $validated['assignment_ids'])
            ->get()
            ->each(function (AuditAssignment $assignment) use (&$deleted, &$blocked): void {
                if ($this->hasAuditData($assignment)) {
                    $blocked++;

                    return;
                }

                DB::transaction(function () use ($assignment): void {
                    $assignment->auditors()->detach();
                    $assignment->delete();
                });
                $deleted++;
            });

        return $blocked > 0
            ? back()->with('status', "{$deleted} penugasan berhasil dihapus.")->with('warning', "{$blocked} penugasan tidak dihapus karena sudah memiliki data audit.")
            : back()->with('status', "{$deleted} penugasan berhasil dihapus.");
    }

    public function notify(AuditAssignment $assignment): RedirectResponse
    {
        if ($assignment->status === 'dibatalkan') {
            return back()->with('warning', 'Notifikasi tidak dikirim karena penugasan sudah dibatalkan.');
        }

        $assignment->load(['auditPeriod', 'unit', 'leadAuditor', 'auditors']);
        $count = $this->notifyAssignmentUsers($assignment);

        return back()->with('status', "Notifikasi penugasan dikirim ke {$count} pengguna terkait.");
    }

    public function printLetter(AuditAssignment $assignment): Response
    {
        $assignment->load(['auditPeriod', 'unit', 'leadAuditor', 'auditors']);
        $auditors = collect([$assignment->leadAuditor->name.' (Lead Auditor)'])
            ->merge($assignment->auditors->where('id', '!=', $assignment->lead_auditor_id)->map(fn (User $auditor): string => $auditor->name.' (Anggota)'))
            ->values();

        $lines = [
            'SURAT TUGAS AUDIT MUTU INTERNAL',
            '',
            'Periode Audit : '.$assignment->auditPeriod->nama,
            'Tahun Akademik : '.$assignment->auditPeriod->tahun_akademik,
            'Unit Auditee : '.$assignment->unit->kode.' - '.$assignment->unit->nama,
            'Tanggal Desk Evaluation : '.($assignment->tanggal_desk_evaluation?->format('d/m/Y') ?? '-'),
            'Jadwal Visitasi : '.($assignment->jadwal_visitasi?->format('d/m/Y') ?? '-'),
            '',
            'Tim Auditor:',
            ...$auditors->map(fn (string $name, int $index): string => ($index + 1).'. '.$name)->all(),
            '',
            'Catatan Penugasan:',
            $assignment->catatan_penugasan ?: '-',
            '',
            'Dokumen ini diterbitkan oleh SIAMI sebagai surat tugas sederhana untuk pelaksanaan audit mutu internal.',
        ];

        $filename = 'surat-tugas-'.$assignment->id.'.pdf';

        return response(SimplePdf::document($lines, reportPrintSettings()), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function createRules(): array
    {
        return [
            'audit_period_id' => ['required', 'integer', 'exists:audit_periods,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            ...$this->auditorAndScheduleRules(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function updateRules(): array
    {
        return $this->auditorAndScheduleRules();
    }

    /**
     * @return array<string, mixed>
     */
    private function auditorAndScheduleRules(): array
    {
        return [
            'lead_auditor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', UserRole::Auditor->value)->where('is_active', true)),
            ],
            'member_auditor_ids' => ['nullable', 'array'],
            'member_auditor_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', UserRole::Auditor->value)->where('is_active', true)),
            ],
            'tanggal_desk_evaluation' => ['nullable', 'date'],
            'jadwal_visitasi' => ['nullable', 'date'],
            'catatan_penugasan' => ['nullable', 'string'],
        ];
    }

    private function validateSingleActiveAssignment(int $periodId, int $unitId): void
    {
        $exists = AuditAssignment::query()
            ->where('audit_period_id', $periodId)
            ->where('unit_id', $unitId)
            ->where('status', 'aktif')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'unit_id' => 'Unit ini sudah memiliki penugasan aktif pada periode yang dipilih.',
            ]);
        }
    }

    /**
     * @param  array<int, int|string>  $memberIds
     */
    private function syncAuditors(AuditAssignment $assignment, int $leadAuditorId, array $memberIds): void
    {
        $members = collect($memberIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id !== $leadAuditorId)
            ->unique()
            ->values();

        $sync = [
            $leadAuditorId => ['peran_dalam_tim' => 'lead'],
        ];

        foreach ($members as $memberId) {
            $sync[$memberId] = ['peran_dalam_tim' => 'anggota'];
        }

        $assignment->auditors()->sync($sync);
    }

    private function notifyAssignmentUsers(AuditAssignment $assignment): int
    {
        $auditors = collect([$assignment->leadAuditor])
            ->merge($assignment->auditors)
            ->filter()
            ->unique('id');
        $auditees = User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('is_active', true)
            ->where('unit_id', $assignment->unit_id)
            ->get();
        $recipients = $auditors->merge($auditees)->unique('id')->values();

        $recipients->each(function (User $user) use ($assignment): void {
            Notification::sendNotification(
                $user->id,
                'penugasan_dibuat',
                'Penugasan Audit',
                "Anda terhubung dengan penugasan audit unit {$assignment->unit->kode} pada periode {$assignment->auditPeriod->nama}.",
                route('dashboard', absolute: false),
                'audit_assignment',
                $assignment->id,
            );
        });

        return $recipients->count();
    }

    private function withConflictWarning(RedirectResponse $redirect, AuditAssignment $assignment): RedirectResponse
    {
        if (! $assignment->hasConflictOfInterest()) {
            return $redirect;
        }

        return $redirect->with('warning', 'Peringatan konflik kepentingan: ada auditor yang berasal dari unit auditee yang sama.');
    }

    /**
     * @return array{self_evaluation_progress: int, desk_evaluation_status: string, visitasi_status: string, active_findings: int, follow_up_status: string}
     */
    private function summary(AuditAssignment $assignment): array
    {
        return [
            'self_evaluation_progress' => $assignment->progressEvaluasiDiri(),
            'desk_evaluation_status' => $assignment->deskEvaluationStatus(),
            'visitasi_status' => $assignment->visitasiStatus(),
            'active_findings' => $assignment->activeFindingsCount(),
            'follow_up_status' => $assignment->followUpStatus(),
        ];
    }

    private function auditorQuery()
    {
        return User::query()
            ->where('role', UserRole::Auditor->value)
            ->where('is_active', true);
    }

    private function hasAuditData(AuditAssignment $assignment): bool
    {
        return $assignment->selfAssessments()->exists()
            || $assignment->evaluations()->exists()
            || $assignment->findings()->exists()
            || $assignment->visit()->exists()
            || DB::table('clarifications')->where('assignment_id', $assignment->id)->exists();
    }
}

<?php

namespace App\Http\Controllers\Auditor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\Clarification;
use App\Models\Evaluation;
use App\Models\Evidence;
use App\Models\Instrument;
use App\Models\Notification;
use App\Models\SelfAssessment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DeskEvaluationController extends Controller
{
    public function index(Request $request): View
    {
        $assignments = $this->assignmentQuery($request)
            ->with(['auditPeriod', 'unit', 'leadAuditor', 'auditors'])
            ->paginate(10);

        $assignments->getCollection()->each(function (AuditAssignment $assignment): void {
            $this->ensureEvaluationRecords($assignment);
            $assignment->load('evaluations');
        });

        return view('auditor.desk-evaluations.index', [
            'assignments' => $assignments,
        ]);
    }

    public function show(Request $request, AuditAssignment $assignment): View
    {
        $this->authorizeAssignment($request, $assignment);
        $this->ensureEvaluationRecords($assignment);

        $assignment->load([
            'auditPeriod',
            'unit',
            'evaluations.instrument.standard',
            'evaluations.selfAssessment.evidences',
            'evaluations.examiner',
        ]);

        return view('auditor.desk-evaluations.show', [
            'assignment' => $assignment,
            'standards' => $this->groupByStandard($assignment),
            'summary' => $this->summary($assignment),
            'statusBuktiOptions' => Evaluation::statusBuktiOptions(),
            'statusPemeriksaanOptions' => Evaluation::statusPemeriksaanOptions(),
            'isFinalized' => $this->isFinalized($assignment),
            'canFinalize' => $this->canFinalize($assignment),
        ]);
    }

    public function update(Request $request, AuditAssignment $assignment, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $this->authorizeEvaluation($assignment, $evaluation);

        if ($this->isFinalized($assignment)) {
            return back()->with('warning', 'Desk evaluation sudah final dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'skor' => ['nullable', 'numeric', 'min:0'],
            'status_bukti' => ['required', 'in:belum_diperiksa,valid,perlu_klarifikasi,tidak_tersedia'],
            'catatan_auditor' => ['nullable', 'string'],
            'usulan_temuan' => ['nullable', 'boolean'],
            'rekomendasi_awal' => ['nullable', 'string'],
        ]);

        $wasUnstarted = $evaluation->status_pemeriksaan === 'belum_dimulai';
        $previousStatusBukti = $evaluation->status_bukti;
        $previousNote = (string) $evaluation->catatan_auditor;
        $previousFindingFlag = (bool) $evaluation->usulan_temuan;

        $evaluation->update([
            ...$validated,
            'usulan_temuan' => $request->boolean('usulan_temuan'),
            'rekomendasi_awal' => $request->boolean('usulan_temuan') ? ($validated['rekomendasi_awal'] ?? null) : null,
            'status_pemeriksaan' => $validated['status_bukti'] === 'perlu_klarifikasi' ? 'menunggu_klarifikasi' : 'berlangsung',
            'diperiksa_oleh' => $request->user()->id,
        ]);

        $this->syncEvidenceVerification($evaluation);

        if (
            $wasUnstarted
            || $previousStatusBukti !== $evaluation->status_bukti
            || $previousNote !== (string) $evaluation->catatan_auditor
            || $previousFindingFlag !== (bool) $evaluation->usulan_temuan
        ) {
            $this->notifyAuditeesAboutEvaluationUpdate($evaluation);
        }

        return back()->with('status', 'Evaluasi instrumen berhasil disimpan.');
    }

    public function requestClarification(Request $request, AuditAssignment $assignment, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $this->authorizeEvaluation($assignment, $evaluation);

        if ($this->isFinalized($assignment)) {
            return back()->with('warning', 'Desk evaluation sudah final dan klarifikasi tidak dapat dikirim dari halaman ini.');
        }

        $validated = $request->validate([
            'catatan_auditor' => ['nullable', 'string'],
            'rekomendasi_awal' => ['nullable', 'string'],
        ]);

        $clarification = DB::transaction(function () use ($assignment, $evaluation, $request, $validated): Clarification {
            $evaluation->update([
                'status_bukti' => 'perlu_klarifikasi',
                'catatan_auditor' => $validated['catatan_auditor'] ?? $evaluation->catatan_auditor,
                'rekomendasi_awal' => $validated['rekomendasi_awal'] ?? $evaluation->rekomendasi_awal,
                'status_pemeriksaan' => 'menunggu_klarifikasi',
                'diperiksa_oleh' => $request->user()->id,
            ]);
            $evaluation->selfAssessment->update(['status' => 'perlu_klarifikasi']);
            $evaluation->selfAssessment->evidences()->update(['status_verifikasi' => 'perlu_klarifikasi']);

            $clarification = Clarification::query()->create([
                'assignment_id' => $assignment->id,
                'instrument_id' => $evaluation->instrument_id,
                'dibuka_oleh' => $request->user()->id,
                'status' => 'terbuka',
            ]);

            $clarification->messages()->create([
                'pengirim_id' => $request->user()->id,
                'isi_pesan' => $validated['catatan_auditor']
                    ?? $evaluation->catatan_auditor
                    ?? 'Mohon berikan klarifikasi untuk instrumen ini.',
            ]);

            return $clarification;
        });

        $clarification->loadMissing([
            'assignment.unit',
            'assignment.auditPeriod',
            'instrument.standard',
            'openedBy',
        ]);

        $message = sprintf(
            '%s meminta klarifikasi untuk unit %s pada periode %s. Instrumen: %s - %s. Catatan: %s',
            $clarification->openedBy?->name ?? 'Auditor',
            $clarification->assignment->unit->nama,
            $clarification->assignment->auditPeriod->nama,
            $clarification->instrument->kode,
            $clarification->instrument->standard->nama,
            $validated['catatan_auditor'] ?? $evaluation->catatan_auditor ?? 'Mohon berikan klarifikasi untuk instrumen ini.'
        );

        User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('unit_id', $assignment->unit_id)
            ->where('is_active', true)
            ->get()
            ->each(function (User $user) use ($clarification, $message): void {
                Notification::sendNotification(
                    $user->id,
                    'klarifikasi_dibuat',
                    'Klarifikasi Auditor',
                    $message,
                    route('auditee.clarifications.show', $clarification, absolute: false),
                    'clarification',
                    $clarification->id,
                );
            });

        return redirect()
            ->route('auditor.clarifications.show', $clarification)
            ->with('status', 'Klarifikasi untuk instrumen ini telah dikirim ke Auditee.');
    }

    public function finalize(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $this->ensureEvaluationRecords($assignment);

        if (! $this->canFinalize($assignment)) {
            return back()->with('warning', 'Finalisasi hanya dapat dilakukan jika semua instrumen sudah mulai diperiksa.');
        }

        DB::transaction(function () use ($assignment): void {
            $assignment->evaluations()->update(['status_pemeriksaan' => 'final']);
            $assignment->selfAssessments()
                ->where('status', '!=', 'perlu_klarifikasi')
                ->update(['status' => 'final']);
        });

        return back()->with('status', 'Desk evaluation berhasil difinalisasi.');
    }

    public function downloadEvidence(Request $request, Evidence $evidence): BinaryFileResponse
    {
        $assessment = $evidence->selfAssessment;
        abort_unless($assessment, 404);
        $this->authorizeAssignment($request, $assessment->assignment);
        abort_unless($evidence->path_file && Storage::disk('public')->exists($evidence->path_file), 404);

        return response()->download(Storage::disk('public')->path($evidence->path_file), $evidence->nama_dokumen);
    }

    public function previewEvidence(Request $request, Evidence $evidence): BinaryFileResponse
    {
        $assessment = $evidence->selfAssessment;
        abort_unless($assessment, 404);
        $this->authorizeAssignment($request, $assessment->assignment);
        abort_unless($evidence->path_file && Storage::disk('public')->exists($evidence->path_file), 404);

        return response()->file(Storage::disk('public')->path($evidence->path_file));
    }

    private function assignmentQuery(Request $request)
    {
        $user = $request->user();

        return AuditAssignment::query()
            ->where('status', 'aktif')
            ->whereHas('auditPeriod', fn ($query) => $query->where('status', 'aktif'))
            ->where(function ($query) use ($user): void {
                $query->where('lead_auditor_id', $user->id)
                    ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $user->id));
            })
            ->latest('id');
    }

    private function authorizeAssignment(Request $request, AuditAssignment $assignment): void
    {
        $allowed = AuditAssignment::query()
            ->whereKey($assignment->id)
            ->where(function ($query) use ($request): void {
                $query->where('lead_auditor_id', $request->user()->id)
                    ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $request->user()->id));
            })
            ->exists();

        abort_unless($allowed, 403);
    }

    private function authorizeEvaluation(AuditAssignment $assignment, Evaluation $evaluation): void
    {
        abort_unless((int) $evaluation->assignment_id === (int) $assignment->id, 403);
    }

    private function ensureEvaluationRecords(AuditAssignment $assignment): void
    {
        foreach (Instrument::query()->where('is_active', true)->with('standard')->get() as $instrument) {
            $assessment = SelfAssessment::query()->firstOrCreate(
                ['assignment_id' => $assignment->id, 'instrument_id' => $instrument->id],
                ['target' => $instrument->target_kriteria, 'status' => 'belum_diisi'],
            );

            Evaluation::query()->firstOrCreate(
                ['assignment_id' => $assignment->id, 'instrument_id' => $instrument->id],
                ['self_assessment_id' => $assessment->id, 'status_pemeriksaan' => 'belum_dimulai'],
            );
        }
    }

    private function groupByStandard(AuditAssignment $assignment)
    {
        return $assignment->evaluations
            ->sortBy([
                fn (Evaluation $evaluation) => $evaluation->instrument->standard->urutan,
                fn (Evaluation $evaluation) => $evaluation->instrument->urutan,
            ])
            ->groupBy(fn (Evaluation $evaluation) => $evaluation->instrument->standard->id)
            ->map(function ($evaluations) {
                return [
                    'standard' => $evaluations->first()->instrument->standard,
                    'evaluations' => $evaluations,
                ];
            });
    }

    /**
     * @return array{total: int, valid_evidences: int, clarification_evidences: int, proposed_findings: int, checked: int}
     */
    private function summary(AuditAssignment $assignment): array
    {
        $evaluations = $assignment->evaluations;

        return [
            'total' => $evaluations->count(),
            'valid_evidences' => $evaluations->where('status_bukti', 'valid')->count(),
            'clarification_evidences' => $evaluations->where('status_bukti', 'perlu_klarifikasi')->count(),
            'proposed_findings' => $evaluations->where('usulan_temuan', true)->count(),
            'checked' => $evaluations->where('status_pemeriksaan', '!=', 'belum_dimulai')->count(),
        ];
    }

    private function canFinalize(AuditAssignment $assignment): bool
    {
        $this->ensureEvaluationRecords($assignment);

        return $assignment->evaluations()->where('status_pemeriksaan', 'belum_dimulai')->doesntExist();
    }

    private function isFinalized(AuditAssignment $assignment): bool
    {
        return $assignment->evaluations()->exists()
            && $assignment->evaluations()->where('status_pemeriksaan', '!=', 'final')->doesntExist();
    }

    private function syncEvidenceVerification(Evaluation $evaluation): void
    {
        if (! in_array($evaluation->status_bukti, ['valid', 'perlu_klarifikasi'], true)) {
            return;
        }

        $evaluation->selfAssessment->evidences()->update([
            'status_verifikasi' => $evaluation->status_bukti,
        ]);

        if ($evaluation->status_bukti === 'perlu_klarifikasi') {
            $evaluation->selfAssessment->update(['status' => 'perlu_klarifikasi']);
        }
    }

    private function notifyAuditeesAboutEvaluationUpdate(Evaluation $evaluation): void
    {
        $evaluation->loadMissing([
            'assignment.unit',
            'assignment.auditPeriod',
            'instrument',
            'examiner',
        ]);

        $assignment = $evaluation->assignment;
        $instrument = $evaluation->instrument;
        $statusBukti = Evaluation::statusBuktiOptions()[$evaluation->status_bukti] ?? $evaluation->status_bukti;
        $auditorName = $evaluation->examiner?->name ?? 'Auditor';

        User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('unit_id', $assignment->unit_id)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $user) => Notification::sendNotification(
                $user->id,
                'desk_evaluation_diperbarui',
                'Desk Evaluation Diperbarui',
                "{$auditorName} telah memperbarui desk evaluation untuk unit {$assignment->unit->nama} pada periode {$assignment->auditPeriod->nama}. Instrumen terkait: {$instrument->kode} - {$instrument->pertanyaan}. Status bukti: {$statusBukti}. Catatan auditor: ".($evaluation->catatan_auditor ?: '-').'.',
                route('auditee.self-assessments.edit', $evaluation->self_assessment_id, absolute: false),
                'evaluation',
                $evaluation->id,
            ));
    }
}

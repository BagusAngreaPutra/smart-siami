<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\Evidence;
use App\Models\Instrument;
use App\Models\Notification;
use App\Models\SelfAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SelfAssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $assignment = $this->resolveAssignment($request);

        if ($assignment) {
            $this->ensureSelfAssessments($assignment);
            $assignment->load(['auditPeriod', 'selfAssessments.instrument.standard', 'selfAssessments.evidences']);
        }

        return view('auditee.self-assessments.index', [
            'assignment' => $assignment,
            'assignments' => $this->assignmentQuery($request)->get(),
            'standards' => $assignment ? $this->groupByStandard($assignment) : collect(),
            'statusOptions' => SelfAssessment::statusOptions(),
            'canFinalize' => $assignment ? $this->canFinalize($assignment) : false,
            'isEditablePeriod' => $assignment ? $this->isEditablePeriod($assignment) : false,
        ]);
    }

    public function edit(Request $request, SelfAssessment $assessment): View
    {
        $this->authorizeAssessment($request, $assessment);
        $this->ensureSelfAssessments($assessment->assignment);

        $assessment->load(['assignment.auditPeriod', 'instrument.standard', 'evidences']);
        $ordered = $assessment->assignment->selfAssessments()
            ->with('instrument.standard')
            ->join('instruments', 'self_assessments.instrument_id', '=', 'instruments.id')
            ->join('standards', 'instruments.standard_id', '=', 'standards.id')
            ->orderBy('standards.urutan')
            ->orderBy('instruments.urutan')
            ->select('self_assessments.*')
            ->get();
        $currentIndex = $ordered->search(fn (SelfAssessment $item): bool => $item->id === $assessment->id);

        return view('auditee.self-assessments.form', [
            'assessment' => $assessment,
            'statusOptions' => SelfAssessment::statusOptions(),
            'canEdit' => $this->canEditAssessment($assessment),
            'previous' => $currentIndex > 0 ? $ordered[$currentIndex - 1] : null,
            'next' => $currentIndex !== false && $currentIndex < $ordered->count() - 1 ? $ordered[$currentIndex + 1] : null,
        ]);
    }

    public function saveDraft(Request $request, SelfAssessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($request, $assessment);
        $this->ensureCanEdit($assessment);

        $assessment->update([
            ...$this->validatedAnswer($request, $assessment->instrument),
            'status' => 'draft',
        ]);

        return back()->with('status', 'Draft evaluasi diri berhasil disimpan.');
    }

    public function submit(Request $request, SelfAssessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($request, $assessment);
        $this->ensureCanEdit($assessment);

        $wasSubmitted = $assessment->status === 'dikirim';
        $assessment->update([
            ...$this->validatedAnswer($request, $assessment->instrument),
            'status' => 'dikirim',
        ]);

        if (! $wasSubmitted) {
            $this->notifyAuditorsAboutSubmittedInstrument($assessment);
        }

        return redirect()->route('auditee.self-evaluations')->with('status', 'Instrumen ditandai selesai dan dikirim.');
    }

    public function withdraw(Request $request, SelfAssessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($request, $assessment);

        if ($assessment->status !== 'dikirim' || ! $this->isEditablePeriod($assessment->assignment)) {
            return back()->with('warning', 'Jawaban tidak dapat ditarik kembali pada status atau periode saat ini.');
        }

        $assessment->update(['status' => 'draft']);

        return back()->with('status', 'Jawaban berhasil ditarik kembali menjadi draft.');
    }

    public function finalize(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $this->ensureSelfAssessments($assignment);

        if (! $this->isEditablePeriod($assignment)) {
            return back()->with('warning', 'Evaluasi diri tidak dapat difinalisasi karena periode sudah ditutup atau melewati batas waktu.');
        }

        if (! $this->canFinalize($assignment)) {
            return back()->with('warning', 'Finalisasi hanya dapat dilakukan jika semua instrumen sudah dikirim atau final.');
        }

        $assignment->selfAssessments()
            ->where('status', 'dikirim')
            ->update(['status' => 'final']);

        $assignment->loadMissing(['unit', 'auditors', 'leadAuditor']);
        collect([$assignment->leadAuditor])
            ->merge($assignment->auditors)
            ->filter()
            ->unique('id')
            ->each(fn ($auditor) => Notification::sendNotification(
                $auditor->id,
                'evaluasi_diri_dikirim',
                'Evaluasi Diri Dikirim',
                "Evaluasi diri unit {$assignment->unit->kode} telah difinalisasi.",
                route('auditor.desk-evaluation.show', $assignment, absolute: false),
                'audit_assignment',
                $assignment->id,
            ));

        return back()->with('status', 'Evaluasi diri berhasil difinalisasi dan dikunci.');
    }

    public function storeEvidence(Request $request, SelfAssessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($request, $assessment);
        $this->ensureCanEdit($assessment);

        $payload = $this->validatedEvidence($request);
        $instrument = $assessment->instrument;

        if ($payload['tipe_sumber'] === 'tautan') {
            Evidence::query()->create([
                ...Arr::except($payload, ['file', 'files']),
                'self_assessment_id' => $assessment->id,
                'uploaded_by' => $request->user()->id,
                'instrumen_terkait' => $instrument->kode,
                'instrument_ids' => [$instrument->id],
            ]);

            return back()->with('status', 'Bukti pendukung berhasil ditambahkan.');
        }

        $files = collect($request->file('files', []))
            ->when($request->hasFile('file'), fn ($files) => $files->prepend($request->file('file')))
            ->values();

        foreach ($files as $index => $file) {
            $documentName = $files->count() === 1
                ? $payload['nama_dokumen']
                : $payload['nama_dokumen'].' - '.$file->getClientOriginalName();

            Evidence::query()->create([
                ...Arr::except($payload, ['file', 'files']),
                'nama_dokumen' => $documentName,
                'path_file' => $file->store('evidences', 'public'),
                'ukuran_file' => $file->getSize(),
                'url_tautan' => null,
                'self_assessment_id' => $assessment->id,
                'uploaded_by' => $request->user()->id,
                'instrumen_terkait' => $instrument->kode,
                'instrument_ids' => [$instrument->id],
            ]);
        }

        return back()->with('status', $files->count().' bukti pendukung berhasil ditambahkan.');
    }

    public function deleteEvidence(Request $request, Evidence $evidence): RedirectResponse
    {
        $assessment = $evidence->selfAssessment;

        if (! $assessment) {
            return back()->with('warning', 'Bukti ini dikelola melalui repositori bukti dokumen.');
        }

        $this->authorizeAssessment($request, $assessment);
        $this->ensureCanEdit($assessment);

        if ($evidence->status_verifikasi !== 'belum_diperiksa') {
            return back()->with('warning', 'Bukti yang sudah diperiksa auditor tidak dapat dihapus.');
        }

        if ($evidence->path_file) {
            Storage::disk('public')->delete($evidence->path_file);
        }

        $evidence->delete();

        return back()->with('status', 'Bukti pendukung berhasil dihapus.');
    }

    private function resolveAssignment(Request $request): ?AuditAssignment
    {
        $query = $this->assignmentQuery($request);

        if ($request->filled('assignment_id')) {
            return $query->whereKey($request->integer('assignment_id'))->first();
        }

        return $query->first();
    }

    private function assignmentQuery(Request $request)
    {
        return AuditAssignment::query()
            ->with('auditPeriod')
            ->where('unit_id', $request->user()->unit_id)
            ->where('status', 'aktif')
            ->whereHas('auditPeriod', fn ($query) => $query->whereIn('status', ['aktif', 'ditutup']))
            ->latest('id');
    }

    private function ensureSelfAssessments(AuditAssignment $assignment): void
    {
        $instruments = Instrument::query()
            ->where('is_active', true)
            ->with('standard')
            ->get();

        foreach ($instruments as $instrument) {
            SelfAssessment::query()->firstOrCreate(
                ['assignment_id' => $assignment->id, 'instrument_id' => $instrument->id],
                ['target' => $instrument->target_kriteria, 'status' => 'belum_diisi'],
            );
        }
    }

    private function groupByStandard(AuditAssignment $assignment)
    {
        return $assignment->selfAssessments
            ->sortBy([
                fn (SelfAssessment $assessment) => $assessment->instrument->standard->urutan,
                fn (SelfAssessment $assessment) => $assessment->instrument->urutan,
            ])
            ->groupBy(fn (SelfAssessment $assessment) => $assessment->instrument->standard->id)
            ->map(function ($assessments) {
                $standard = $assessments->first()->instrument->standard;
                $total = $assessments->count();
                $done = $assessments->whereIn('status', ['dikirim', 'final'])->count();

                return [
                    'standard' => $standard,
                    'assessments' => $assessments,
                    'progress' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
                ];
            });
    }

    private function canFinalize(AuditAssignment $assignment): bool
    {
        $this->ensureSelfAssessments($assignment);
        $statuses = $assignment->selfAssessments()->pluck('status');

        return $statuses->isNotEmpty()
            && $statuses->every(fn (string $status): bool => in_array($status, ['dikirim', 'final'], true));
    }

    private function notifyAuditorsAboutSubmittedInstrument(SelfAssessment $assessment): void
    {
        $assessment->loadMissing([
            'assignment.unit',
            'assignment.auditPeriod',
            'assignment.leadAuditor',
            'assignment.auditors',
            'instrument',
        ]);

        $assignment = $assessment->assignment;
        $instrument = $assessment->instrument;

        collect([$assignment->leadAuditor])
            ->merge($assignment->auditors)
            ->filter()
            ->unique('id')
            ->each(fn ($auditor) => Notification::sendNotification(
                $auditor->id,
                'evaluasi_diri_dikirim',
                'Evaluasi Diri Instrumen Dikirim',
                "Auditee unit {$assignment->unit->nama} telah mengirim jawaban evaluasi diri untuk instrumen {$instrument->kode} pada periode {$assignment->auditPeriod->nama}. Pertanyaan: {$instrument->pertanyaan}. Silakan buka Desk Evaluation untuk meninjau jawaban dan bukti pendukung.",
                route('auditor.desk-evaluation.show', $assignment, absolute: false),
                'audit_assignment',
                $assignment->id,
            ));
    }

    private function isEditablePeriod(AuditAssignment $assignment): bool
    {
        return $assignment->status === 'aktif'
            && $assignment->auditPeriod->status === 'aktif'
            && now()->toDateString() <= $assignment->auditPeriod->batas_evaluasi_diri->toDateString();
    }

    private function canEditAssessment(SelfAssessment $assessment): bool
    {
        if ($assessment->status === 'final') {
            return false;
        }

        if ($assessment->status === 'perlu_klarifikasi') {
            return $assessment->assignment->auditPeriod->status === 'aktif';
        }

        if ($assessment->status === 'dikirim') {
            return false;
        }

        return $this->isEditablePeriod($assessment->assignment);
    }

    private function ensureCanEdit(SelfAssessment $assessment): void
    {
        abort_unless($this->canEditAssessment($assessment), 403, 'Evaluasi diri tidak dapat diubah pada status atau periode saat ini.');
    }

    private function authorizeAssessment(Request $request, SelfAssessment $assessment): void
    {
        $assessment->loadMissing('assignment.auditPeriod', 'instrument');
        $this->authorizeAssignment($request, $assessment->assignment);
    }

    private function authorizeAssignment(Request $request, AuditAssignment $assignment): void
    {
        abort_unless($assignment->unit_id === $request->user()->unit_id, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAnswer(Request $request, Instrument $instrument): array
    {
        $rules = [
            'jawaban_naratif' => ['nullable', 'string'],
            'realisasi' => ['nullable', 'string'],
            'kendala' => ['nullable', 'string'],
            'analisis_gap' => ['nullable', 'string'],
            'rencana_perbaikan_awal' => ['nullable', 'string'],
        ];

        if (in_array($instrument->jenis_jawaban, ['angka', 'skor'], true)) {
            $rules['realisasi'] = ['nullable', 'numeric'];
        }

        return [
            ...$request->validate($rules),
            'target' => $instrument->target_kriteria,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedEvidence(Request $request): array
    {
        $payload = $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'jenis_dokumen' => ['nullable', 'string', 'max:255'],
            'tipe_sumber' => ['required', 'in:file,tautan'],
            'file' => ['nullable', 'file', 'mimes:'.implode(',', allowedUploadExtensions()), 'max:'.maxUploadKilobytes()],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:'.implode(',', allowedUploadExtensions()), 'max:'.maxUploadKilobytes()],
            'url_tautan' => ['required_if:tipe_sumber,tautan', 'nullable', 'url', 'max:255'],
            'tahun_dokumen' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        if ($payload['tipe_sumber'] === 'file' && ! $request->hasFile('file') && ! $request->hasFile('files')) {
            throw ValidationException::withMessages([
                'files' => 'Pilih minimal satu file bukti pendukung.',
            ]);
        }

        return $payload;
    }
}

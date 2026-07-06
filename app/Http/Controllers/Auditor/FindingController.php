<?php

namespace App\Http\Controllers\Auditor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Finding;
use App\Models\Instrument;
use App\Models\Notification;
use App\Models\Standard;
use App\Models\Unit;
use App\Models\User;
use App\Support\SimplePdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FindingController extends Controller
{
    public function index(Request $request): View
    {
        $this->markOverdueFindings($request);

        $query = $this->findingQuery($request)
            ->with(['assignment.unit', 'assignment.auditPeriod', 'standard', 'instrument'])
            ->latest('id');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->string('kategori')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('unit_id')) {
            $query->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->integer('unit_id')));
        }

        if ($request->filled('audit_period_id')) {
            $query->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $request->integer('audit_period_id')));
        }

        return view('auditor.findings.index', [
            'findings' => $query->paginate(10)->withQueryString(),
            'kanbanFindings' => (clone $query)
                ->with(['assignment.unit', 'assignment.leadAuditor', 'creator', 'standard'])
                ->limit(80)
                ->get()
                ->groupBy('status'),
            'kategoriOptions' => Finding::kategoriOptions(),
            'statusOptions' => Finding::statusOptions(),
            'units' => Unit::query()->whereHas('auditAssignments', fn ($query) => $this->scopeAssignmentToAuditor($request, $query))->orderBy('nama')->get(),
            'periods' => AuditPeriod::query()->whereHas('assignments', fn ($query) => $this->scopeAssignmentToAuditor($request, $query))->latest('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->formView($request, new Finding([
            'prioritas' => 'sedang',
            'status' => 'draft',
            'target_penyelesaian' => now()->addDays(30),
            'dibuat_oleh' => $request->user()->id,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);
        $assignment = $this->assignmentQuery($request)->whereKey($payload['assignment_id'])->firstOrFail();
        $instrument = $this->validateInstrument($payload['standard_id'], $payload['instrument_id']);

        $finding = Finding::query()->create([
            ...$payload,
            'assignment_id' => $assignment->id,
            'standard_id' => $instrument->standard_id,
            'instrument_id' => $instrument->id,
            'status' => 'draft',
            'dibuat_oleh' => $request->user()->id,
        ]);

        $this->recordHistory($finding, null, 'draft', $request->user()->id, 'Temuan dibuat sebagai draft.');

        return redirect()->route('auditor.findings.show', $finding)->with('status', 'Draft temuan berhasil disimpan.');
    }

    public function show(Request $request, Finding $finding): View
    {
        $this->authorizeFinding($request, $finding);
        $this->markFindingOverdue($finding, $request->user()->id);

        $finding->load([
            'assignment.unit',
            'assignment.auditPeriod',
            'standard',
            'instrument',
            'creator',
            'finalizer',
            'histories.changer',
        ]);

        return view('auditor.findings.show', [
            'finding' => $finding,
            'kategoriOptions' => Finding::kategoriOptions(),
            'prioritasOptions' => Finding::prioritasOptions(),
            'statusOptions' => Finding::statusOptions(),
        ]);
    }

    public function edit(Request $request, Finding $finding): View
    {
        $this->authorizeFinding($request, $finding);
        $this->markFindingOverdue($finding, $request->user()->id);

        return $this->formView($request, $finding);
    }

    public function update(Request $request, Finding $finding): RedirectResponse
    {
        $this->authorizeFinding($request, $finding);

        if ($finding->status === 'dibatalkan') {
            return back()->with('warning', 'Temuan yang dibatalkan tidak dapat diubah.');
        }

        if ($finding->isEditableFreely()) {
            $payload = $this->validatedPayload($request);
            $assignment = $this->assignmentQuery($request)->whereKey($payload['assignment_id'])->firstOrFail();
            $instrument = $this->validateInstrument($payload['standard_id'], $payload['instrument_id']);

            $finding->update([
                ...$payload,
                'assignment_id' => $assignment->id,
                'standard_id' => $instrument->standard_id,
                'instrument_id' => $instrument->id,
            ]);

            return redirect()->route('auditor.findings.show', $finding)->with('status', 'Draft temuan berhasil diperbarui.');
        }

        $payload = $request->validate([
            'kategori' => ['required', Rule::in(array_keys(Finding::kategoriOptions()))],
            'target_penyelesaian' => ['required', 'date'],
        ]);

        foreach (['kategori', 'target_penyelesaian'] as $field) {
            $oldValue = $field === 'target_penyelesaian'
                ? $finding->target_penyelesaian->toDateString()
                : (string) $finding->{$field};
            $newValue = (string) $payload[$field];

            if ($oldValue !== $newValue) {
                $finding->histories()->create([
                    'dari_status' => $finding->status,
                    'ke_status' => $finding->status,
                    'field' => $field,
                    'nilai_lama' => $oldValue,
                    'nilai_baru' => $newValue,
                    'catatan' => 'Perubahan terbatas setelah temuan aktif.',
                    'changed_by' => $request->user()->id,
                ]);
            }
        }

        $finding->update($payload);

        return redirect()->route('auditor.findings.show', $finding)->with('status', 'Temuan aktif berhasil diperbarui terbatas.');
    }

    public function finalize(Request $request, Finding $finding): RedirectResponse
    {
        $this->authorizeFinding($request, $finding);

        if ($finding->status !== 'draft') {
            return back()->with('warning', 'Hanya temuan draft yang dapat difinalisasi.');
        }

        DB::transaction(function () use ($finding, $request): void {
            $finding->loadMissing('assignment.auditPeriod', 'assignment.unit');

            $finding->update([
                'nomor_temuan' => $finding->nomor_temuan ?: $this->generateFindingNumber($finding),
                'status' => 'aktif',
                'difinalisasi_oleh' => $request->user()->id,
                'waktu_finalisasi' => now(),
            ]);

            $this->recordHistory($finding, 'draft', 'aktif', $request->user()->id, 'Temuan difinalisasi dan dikirim ke auditee.');
        });

        $finding->refresh();
        $this->notifyAuditees($finding);

        return redirect()->route('auditor.findings.show', $finding)->with('status', 'Temuan difinalisasi dan dikirim ke Auditee.');
    }

    public function cancel(Request $request, Finding $finding): RedirectResponse
    {
        $this->authorizeFinding($request, $finding);

        if ($finding->hasFollowUps()) {
            return back()->with('warning', 'Temuan yang sudah memiliki tindak lanjut tidak dapat dibatalkan.');
        }

        if ($finding->status === 'dibatalkan') {
            return back()->with('warning', 'Temuan sudah dibatalkan.');
        }

        $payload = $request->validate([
            'alasan_pembatalan' => ['required', 'string'],
        ]);
        $oldStatus = $finding->status;

        $finding->update([
            'status' => 'dibatalkan',
            'alasan_pembatalan' => $payload['alasan_pembatalan'],
        ]);

        $this->recordHistory($finding, $oldStatus, 'dibatalkan', $request->user()->id, $payload['alasan_pembatalan']);

        return back()->with('status', 'Temuan berhasil dibatalkan dengan alasan tersimpan.');
    }

    public function print(Request $request): Response
    {
        $this->markOverdueFindings($request);

        $period = null;
        $query = $this->findingQuery($request)
            ->with(['assignment.unit', 'assignment.auditPeriod', 'standard'])
            ->whereNotNull('nomor_temuan')
            ->latest('id');

        if ($request->filled('audit_period_id')) {
            $period = AuditPeriod::query()->find($request->integer('audit_period_id'));
            $query->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $request->integer('audit_period_id')));
        }

        $findings = $query->get();
        $lines = [
            'DAFTAR TEMUAN AUDIT',
            '',
            'Periode : '.($period?->nama ?? 'Semua periode penugasan auditor'),
            '',
        ];

        foreach ($findings as $index => $finding) {
            $lines[] = ($index + 1).'. '.$finding->nomor_temuan.' | '.$finding->assignment->unit->kode.' | '.(Finding::kategoriOptions()[$finding->kategori] ?? $finding->kategori);
            $lines[] = '   Standar: '.$finding->standard->kode.' - Target: '.$finding->target_penyelesaian->format('d/m/Y').' - Status: '.(Finding::statusOptions()[$finding->status] ?? $finding->status);
            $lines[] = '   Rekomendasi: '.$finding->rekomendasi_auditor;
        }

        if ($findings->isEmpty()) {
            $lines[] = 'Belum ada temuan final untuk filter ini.';
        }

        return response(SimplePdf::document($lines, reportPrintSettings()), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="daftar-temuan.pdf"',
        ]);
    }

    private function formView(Request $request, Finding $finding): View
    {
        $finding->loadMissing(['assignment', 'instrument']);

        return view('auditor.findings.form', [
            'finding' => $finding,
            'assignments' => $this->assignmentQuery($request)->with(['auditPeriod', 'unit'])->get(),
            'standards' => Standard::query()->with(['instruments' => fn ($query) => $query->where('is_active', true)->orderBy('urutan')])->orderBy('urutan')->get(),
            'kategoriOptions' => Finding::kategoriOptions(),
            'prioritasOptions' => Finding::prioritasOptions(),
            'statusOptions' => Finding::statusOptions(),
            'isLocked' => $finding->exists && ! $finding->isEditableFreely(),
        ]);
    }

    private function assignmentQuery(Request $request)
    {
        return AuditAssignment::query()
            ->where('status', 'aktif')
            ->where(function ($query) use ($request): void {
                $query->where('lead_auditor_id', $request->user()->id)
                    ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $request->user()->id));
            })
            ->latest('id');
    }

    private function findingQuery(Request $request)
    {
        return Finding::query()
            ->whereHas('assignment', fn ($query) => $this->scopeAssignmentToAuditor($request, $query));
    }

    private function scopeAssignmentToAuditor(Request $request, $query): void
    {
        $query->where(function ($query) use ($request): void {
            $query->where('lead_auditor_id', $request->user()->id)
                ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $request->user()->id));
        });
    }

    private function authorizeFinding(Request $request, Finding $finding): void
    {
        abort_unless($this->findingQuery($request)->whereKey($finding->id)->exists(), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'assignment_id' => ['required', 'integer', 'exists:audit_assignments,id'],
            'standard_id' => ['required', 'integer', 'exists:standards,id'],
            'instrument_id' => ['required', 'integer', 'exists:instruments,id'],
            'kategori' => ['required', Rule::in(array_keys(Finding::kategoriOptions()))],
            'prioritas' => ['required', Rule::in(array_keys(Finding::prioritasOptions()))],
            'kondisi_aktual' => ['required', 'string'],
            'kriteria' => ['required', 'string'],
            'bukti_objektif' => ['required', 'string'],
            'akar_masalah_awal' => ['nullable', 'string'],
            'rekomendasi_auditor' => ['required', 'string'],
            'target_penyelesaian' => ['required', 'date'],
        ]);
    }

    private function validateInstrument(int $standardId, int $instrumentId): Instrument
    {
        return Instrument::query()
            ->where('standard_id', $standardId)
            ->whereKey($instrumentId)
            ->firstOrFail();
    }

    private function generateFindingNumber(Finding $finding): string
    {
        $finding->loadMissing('assignment.auditPeriod', 'assignment.unit');
        $periodCode = $this->periodCode($finding->assignment->auditPeriod);
        $prefix = $periodCode.'-'.$finding->assignment->unit->kode;
        $lastNumber = Finding::query()
            ->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $finding->assignment->audit_period_id)->where('unit_id', $finding->assignment->unit_id))
            ->whereNotNull('nomor_temuan')
            ->where('nomor_temuan', 'like', $prefix.'-%')
            ->orderByDesc('nomor_temuan')
            ->value('nomor_temuan');

        $next = $lastNumber ? ((int) substr($lastNumber, -3)) + 1 : 1;

        return $prefix.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function periodCode(AuditPeriod $period): string
    {
        preg_match('/20\d{2}/', $period->tahun_akademik.' '.$period->nama, $matches);

        return 'AMI'.($matches[0] ?? $period->tanggal_mulai->format('Y'));
    }

    private function notifyAuditees(Finding $finding): void
    {
        $finding->loadMissing('assignment');

        User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('unit_id', $finding->assignment->unit_id)
            ->where('is_active', true)
            ->get()
            ->each(function (User $user) use ($finding): void {
                Notification::sendNotification(
                    $user->id,
                    'temuan_difinalisasi',
                    'Temuan Audit Baru',
                    "Temuan {$finding->nomor_temuan} telah difinalisasi dan perlu ditindaklanjuti.",
                    route('auditee.findings-followups.show', $finding, absolute: false),
                    'finding',
                    $finding->id,
                );
            });
    }

    private function markOverdueFindings(Request $request): void
    {
        $this->findingQuery($request)
            ->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi'])
            ->whereDate('target_penyelesaian', '<', now()->toDateString())
            ->get()
            ->each(fn (Finding $finding) => $this->markFindingOverdue($finding, $request->user()->id));
    }

    private function markFindingOverdue(Finding $finding, int $userId): void
    {
        if (! in_array($finding->status, ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi'], true)) {
            return;
        }

        if ($finding->target_penyelesaian->toDateString() >= now()->toDateString()) {
            return;
        }

        $oldStatus = $finding->status;
        $finding->update(['status' => 'terlambat']);
        $this->recordHistory($finding, $oldStatus, 'terlambat', $userId, 'Status otomatis berubah karena target penyelesaian terlewati.');
    }

    private function recordHistory(Finding $finding, ?string $from, string $to, ?int $userId, ?string $note = null): void
    {
        $finding->histories()->create([
            'dari_status' => $from,
            'ke_status' => $to,
            'catatan' => $note,
            'changed_by' => $userId,
        ]);
    }
}

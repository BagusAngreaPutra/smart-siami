<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use App\Models\Finding;
use App\Models\FollowUp;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FollowUpController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->findingQuery($request)
            ->with(['assignment.auditPeriod', 'assignment.unit', 'standard', 'latestFollowUp.latestVerification'])
            ->latest('id');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->string('kategori')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('auditee.follow-ups.index', [
            'findings' => $query->paginate(10)->withQueryString(),
            'kategoriOptions' => Finding::kategoriOptions(),
            'findingStatusOptions' => Finding::statusOptions(),
            'followUpStatusOptions' => FollowUp::statusOptions(),
        ]);
    }

    public function show(Request $request, Finding $finding): View
    {
        $this->authorizeFinding($request, $finding);

        $finding->load([
            'assignment.auditPeriod',
            'assignment.unit',
            'standard',
            'instrument',
            'latestFollowUp.evidences.uploader',
            'latestFollowUp.verifications.verifier',
            'latestFollowUp.latestVerification.verifier',
        ]);

        return view('auditee.follow-ups.show', [
            'finding' => $finding,
            'followUp' => $finding->latestFollowUp,
            'kategoriOptions' => Finding::kategoriOptions(),
            'findingStatusOptions' => Finding::statusOptions(),
            'followUpStatusOptions' => FollowUp::statusOptions(),
            'progresOptions' => FollowUp::progresOptions(),
            'canEdit' => ! $finding->latestFollowUp || $finding->latestFollowUp->canBeEditedByAuditee(),
        ]);
    }

    public function save(Request $request, Finding $finding): RedirectResponse
    {
        $this->authorizeFinding($request, $finding);
        $this->ensureCanWorkOnFinding($finding);

        $followUp = $finding->latestFollowUp;

        if ($followUp && ! $followUp->canBeEditedByAuditee()) {
            return back()->with('warning', 'Tindak lanjut yang sedang diajukan atau sudah disetujui tidak dapat diubah.');
        }

        $payload = $this->validatedPayload($request);

        DB::transaction(function () use ($finding, $followUp, $payload, $request): void {
            FollowUp::query()->updateOrCreate(
                ['id' => $followUp?->id],
                [
                    ...$payload,
                    'finding_id' => $finding->id,
                    'assignment_id' => $finding->assignment_id,
                    'status' => 'draft',
                    'dibuat_oleh' => $followUp?->dibuat_oleh ?? $request->user()->id,
                ],
            );

            if (in_array($finding->status, ['aktif', 'terlambat'], true)) {
                $this->moveFindingStatus($finding, 'dalam_tindak_lanjut', $request->user()->id, 'Auditee menyimpan rencana tindak lanjut.');
            }
        });

        return back()->with('status', 'Draft tindak lanjut berhasil disimpan.');
    }

    public function storeEvidence(Request $request, Finding $finding): RedirectResponse
    {
        $this->authorizeFinding($request, $finding);
        $followUp = $this->editableFollowUp($finding);
        $payload = $this->validatedEvidence($request);
        $file = $request->file('file');

        if ($payload['tipe_sumber'] === 'file') {
            $payload['path_file'] = $file->store('follow-up-evidences', 'public');
            $payload['ukuran_file'] = $file->getSize();
            $payload['url_tautan'] = null;
        }

        Evidence::query()->create([
            ...Arr::except($payload, ['file']),
            'follow_up_id' => $followUp->id,
            'uploaded_by' => $request->user()->id,
            'instrumen_terkait' => $finding->instrument->kode,
            'instrument_ids' => [$finding->instrument_id],
        ]);

        return back()->with('status', 'Bukti penyelesaian berhasil ditambahkan.');
    }

    public function submit(Request $request, Finding $finding): RedirectResponse
    {
        $this->authorizeFinding($request, $finding);
        $followUp = $finding->latestFollowUp;

        if (! $followUp) {
            return back()->with('warning', 'Simpan rencana tindak lanjut terlebih dahulu.');
        }

        if (! $followUp->canBeEditedByAuditee()) {
            return back()->with('warning', 'Tindak lanjut tidak dapat diajukan pada status saat ini.');
        }

        if ($followUp->evidences()->doesntExist()) {
            return back()->with('warning', 'Ajukan verifikasi setelah minimal satu bukti penyelesaian diunggah.');
        }

        DB::transaction(function () use ($finding, $followUp, $request): void {
            $followUp->update(['status' => 'diajukan']);
            $this->moveFindingStatus($finding, 'menunggu_verifikasi', $request->user()->id, 'Auditee mengajukan tindak lanjut untuk diverifikasi.');
        });

        $this->notifyAuditors($followUp);

        return back()->with('status', 'Tindak lanjut berhasil diajukan untuk verifikasi auditor.');
    }

    public function downloadEvidence(Request $request, Evidence $evidence): BinaryFileResponse
    {
        $followUp = $evidence->followUp;
        abort_unless($followUp, 404);
        $this->authorizeFinding($request, $followUp->finding);
        abort_unless($evidence->path_file && Storage::disk('public')->exists($evidence->path_file), 404);

        return response()->download(Storage::disk('public')->path($evidence->path_file), $evidence->nama_dokumen);
    }

    private function findingQuery(Request $request)
    {
        return Finding::query()
            ->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->user()->unit_id))
            ->whereNotIn('status', ['draft', 'dibatalkan']);
    }

    private function authorizeFinding(Request $request, Finding $finding): void
    {
        abort_unless($this->findingQuery($request)->whereKey($finding->id)->exists(), 403);
    }

    private function ensureCanWorkOnFinding(Finding $finding): void
    {
        abort_if(in_array($finding->status, ['ditutup', 'dibatalkan'], true), 403, 'Temuan ini sudah tidak menerima tindak lanjut baru.');
    }

    private function editableFollowUp(Finding $finding): FollowUp
    {
        $followUp = $finding->latestFollowUp;
        abort_unless($followUp, 404, 'Simpan rencana tindak lanjut terlebih dahulu.');
        abort_unless($followUp->canBeEditedByAuditee(), 403, 'Bukti tidak dapat ditambahkan pada status tindak lanjut saat ini.');

        return $followUp;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'rencana_tindakan' => ['required', 'string'],
            'penanggung_jawab' => ['required', 'string', 'max:255'],
            'target_penyelesaian' => ['required', 'date'],
            'indikator_keberhasilan' => ['required', 'string'],
            'progres' => ['required', Rule::in(array_keys(FollowUp::progresOptions()))],
            'kendala' => ['nullable', 'string'],
            'catatan_auditee' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedEvidence(Request $request): array
    {
        return $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'jenis_dokumen' => ['nullable', 'string', 'max:255'],
            'tipe_sumber' => ['required', 'in:file,tautan'],
            'file' => ['required_if:tipe_sumber,file', 'nullable', 'file', 'mimes:'.implode(',', allowedUploadExtensions()), 'max:'.maxUploadKilobytes()],
            'url_tautan' => ['required_if:tipe_sumber,tautan', 'nullable', 'url', 'max:255'],
            'tahun_dokumen' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }

    private function notifyAuditors(FollowUp $followUp): void
    {
        $followUp->loadMissing('assignment.leadAuditor', 'assignment.auditors');
        collect([$followUp->assignment->leadAuditor])
            ->merge($followUp->assignment->auditors)
            ->filter()
            ->unique('id')
            ->each(function ($auditor) use ($followUp): void {
                Notification::sendNotification(
                    $auditor->id,
                    'tindak_lanjut_diajukan',
                    'Tindak Lanjut Diajukan',
                    "Tindak lanjut untuk temuan {$followUp->finding->nomor_temuan} menunggu verifikasi.",
                    route('auditor.follow-up-verifications.show', $followUp, absolute: false),
                    'follow_up',
                    $followUp->id,
                );
            });
    }

    private function moveFindingStatus(Finding $finding, string $status, int $userId, string $note): void
    {
        if ($finding->status === $status) {
            return;
        }

        $oldStatus = $finding->status;
        $finding->update(['status' => $status]);
        $finding->histories()->create([
            'dari_status' => $oldStatus,
            'ke_status' => $status,
            'catatan' => $note,
            'changed_by' => $userId,
        ]);
    }
}

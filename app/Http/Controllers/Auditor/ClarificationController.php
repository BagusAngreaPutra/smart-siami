<?php

namespace App\Http\Controllers\Auditor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditPeriod;
use App\Models\Clarification;
use App\Models\ClarificationEvidence;
use App\Models\Notification;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClarificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->clarificationQuery($request)
            ->with(['assignment.unit', 'assignment.auditPeriod', 'instrument.standard', 'openedBy', 'messages.sender'])
            ->latest();

        if ($request->filled('unit_id')) {
            $query->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->integer('unit_id')));
        }

        if ($request->filled('audit_period_id')) {
            $query->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $request->integer('audit_period_id')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('auditor.clarifications.index', [
            'clarifications' => $query->paginate(10)->withQueryString(),
            'statusOptions' => Clarification::statusOptions(),
            'units' => Unit::query()->whereHas('auditAssignments', fn ($query) => $this->scopeAssignmentToAuditor($request, $query))->orderBy('nama')->get(),
            'periods' => AuditPeriod::query()->whereHas('assignments', fn ($query) => $this->scopeAssignmentToAuditor($request, $query))->latest('id')->get(),
        ]);
    }

    public function show(Request $request, Clarification $clarification): View
    {
        $this->authorizeClarification($request, $clarification);

        $clarification->load([
            'assignment.unit',
            'assignment.auditPeriod',
            'instrument.standard',
            'openedBy',
            'messages.sender',
            'evidences.uploader',
        ]);

        return view('auditor.clarifications.show', [
            'clarification' => $clarification,
            'statusOptions' => Clarification::statusOptions(),
        ]);
    }

    public function storeMessage(Request $request, Clarification $clarification): RedirectResponse
    {
        $this->authorizeClarification($request, $clarification);
        $this->ensureOpenThread($clarification);

        $validated = $request->validate([
            'isi_pesan' => ['required', 'string'],
        ]);

        $clarification->messages()->create([
            'pengirim_id' => $request->user()->id,
            'isi_pesan' => $validated['isi_pesan'],
        ]);

        $notificationCount = $this->notifyAuditees($clarification, 'pesan_baru', $validated['isi_pesan']);

        return back()->with('status', "Pesan klarifikasi berhasil dikirim. Notifikasi dikirim ke {$notificationCount} auditee.");
    }

    public function storeEvidence(Request $request, Clarification $clarification): RedirectResponse
    {
        $this->authorizeClarification($request, $clarification);
        $this->ensureOpenThread($clarification);

        $payload = $this->validatedEvidence($request);
        $file = $request->file('file');

        if ($payload['tipe_sumber'] === 'file') {
            $payload['path_file'] = $file->store('clarification-evidences', 'public');
            $payload['url_tautan'] = null;
        }

        $clarification->evidences()->create([
            ...Arr::except($payload, ['file']),
            'diunggah_oleh' => $request->user()->id,
        ]);

        $notificationCount = $this->notifyAuditees($clarification, 'lampiran_baru', $payload['nama_dokumen']);

        return back()->with('status', "Lampiran klarifikasi berhasil ditambahkan. Notifikasi dikirim ke {$notificationCount} auditee.");
    }

    public function finish(Request $request, Clarification $clarification): RedirectResponse
    {
        $this->authorizeClarification($request, $clarification);
        $clarification->update(['status' => 'selesai']);

        $notificationCount = $this->notifyAuditees($clarification, 'selesai');

        return back()->with('status', "Klarifikasi ditandai selesai. Notifikasi dikirim ke {$notificationCount} auditee.");
    }

    public function reopen(Request $request, Clarification $clarification): RedirectResponse
    {
        $this->authorizeClarification($request, $clarification);
        $clarification->update(['status' => 'dibuka_kembali']);

        $notificationCount = $this->notifyAuditees($clarification, 'dibuka_kembali');

        return back()->with('status', "Klarifikasi dibuka kembali. Notifikasi dikirim ke {$notificationCount} auditee.");
    }

    public function downloadEvidence(Request $request, ClarificationEvidence $evidence): BinaryFileResponse
    {
        $this->authorizeClarification($request, $evidence->clarification);
        abort_unless($evidence->path_file && Storage::disk('public')->exists($evidence->path_file), 404);

        return response()->download(Storage::disk('public')->path($evidence->path_file), $evidence->nama_dokumen);
    }

    private function clarificationQuery(Request $request)
    {
        return Clarification::query()
            ->whereHas('assignment', fn ($query) => $this->scopeAssignmentToAuditor($request, $query));
    }

    private function scopeAssignmentToAuditor(Request $request, $query): void
    {
        $query->where(function ($query) use ($request): void {
            $query->where('lead_auditor_id', $request->user()->id)
                ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $request->user()->id));
        });
    }

    private function authorizeClarification(Request $request, Clarification $clarification): void
    {
        abort_unless(
            $this->clarificationQuery($request)->whereKey($clarification->id)->exists(),
            403
        );
    }

    private function ensureOpenThread(Clarification $clarification): void
    {
        abort_if($clarification->status === 'selesai', 403, 'Klarifikasi sudah selesai. Buka kembali sebelum menambah pesan atau lampiran.');
    }

    private function notifyAuditees(Clarification $clarification, string $event, ?string $detail = null): int
    {
        $clarification->loadMissing([
            'assignment.unit',
            'assignment.auditPeriod',
            'instrument.standard',
            'openedBy',
        ]);

        $unitName = $clarification->assignment->unit->nama;
        $periodName = $clarification->assignment->auditPeriod->nama;
        $instrumentCode = $clarification->instrument->kode;
        $standardName = $clarification->instrument->standard->nama;
        $auditorName = $clarification->openedBy?->name ?? 'Auditor';

        [$type, $title, $message] = match ($event) {
            'dibuka_kembali' => [
                'klarifikasi_dibuka_kembali',
                'Klarifikasi Dibuka Kembali',
                "{$auditorName} membuka kembali klarifikasi untuk unit {$unitName} pada periode {$periodName}. Instrumen: {$instrumentCode} - {$standardName}. Silakan periksa dan lengkapi tindak lanjut yang diminta.",
            ],
            'pesan_baru' => [
                'klarifikasi_pesan_baru',
                'Pesan Baru dari Auditor',
                "{$auditorName} mengirim pesan klarifikasi untuk unit {$unitName} pada periode {$periodName}. Instrumen: {$instrumentCode} - {$standardName}. Pesan: ".($detail ?: '-'),
            ],
            'lampiran_baru' => [
                'klarifikasi_lampiran_baru',
                'Lampiran Klarifikasi Baru',
                "{$auditorName} menambahkan lampiran klarifikasi untuk unit {$unitName} pada periode {$periodName}. Instrumen: {$instrumentCode} - {$standardName}. Lampiran: ".($detail ?: '-'),
            ],
            'selesai' => [
                'klarifikasi_selesai',
                'Klarifikasi Ditandai Selesai',
                "{$auditorName} menandai klarifikasi selesai untuk unit {$unitName} pada periode {$periodName}. Instrumen: {$instrumentCode} - {$standardName}.",
            ],
            default => [
                'klarifikasi_dibuat',
                'Klarifikasi Auditor',
                "{$auditorName} meminta klarifikasi untuk unit {$unitName} pada periode {$periodName}. Instrumen: {$instrumentCode} - {$standardName}.",
            ],
        };

        $auditees = User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('unit_id', $clarification->assignment->unit_id)
            ->where('is_active', true)
            ->get();

        if ($auditees->isEmpty()) {
            Log::warning('Tidak ada auditee aktif penerima notifikasi klarifikasi.', [
                'clarification_id' => $clarification->id,
                'assignment_id' => $clarification->assignment_id,
                'unit_id' => $clarification->assignment->unit_id,
                'event' => $event,
            ]);
        }

        $auditees->each(function (User $user) use ($clarification, $type, $title, $message): void {
                Notification::sendNotification(
                    $user->id,
                    $type,
                    $title,
                    $message,
                    route('auditee.clarifications.show', $clarification, absolute: false),
                    'clarification',
                    $clarification->id,
                );
            });

        return $auditees->count();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedEvidence(Request $request): array
    {
        return $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'tipe_sumber' => ['required', 'in:file,tautan'],
            'file' => ['required_if:tipe_sumber,file', 'nullable', 'file', 'mimes:'.implode(',', allowedUploadExtensions()), 'max:'.maxUploadKilobytes()],
            'url_tautan' => ['required_if:tipe_sumber,tautan', 'nullable', 'url', 'max:255'],
        ]);
    }
}

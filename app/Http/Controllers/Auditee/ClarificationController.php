<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\Clarification;
use App\Models\ClarificationEvidence;
use App\Models\Notification;
use App\Models\SelfAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('auditee.clarifications.index', [
            'clarifications' => $query->paginate(10)->withQueryString(),
            'statusOptions' => Clarification::statusOptions(),
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

        $assessment = SelfAssessment::query()
            ->where('assignment_id', $clarification->assignment_id)
            ->where('instrument_id', $clarification->instrument_id)
            ->first();

        return view('auditee.clarifications.show', [
            'clarification' => $clarification,
            'statusOptions' => Clarification::statusOptions(),
            'assessment' => $assessment,
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

        $clarification->update(['status' => 'dijawab']);
        Notification::sendNotification(
            $clarification->dibuka_oleh,
            'klarifikasi_dijawab',
            'Klarifikasi Dijawab',
            'Auditee telah menjawab klarifikasi auditor.',
            route('auditor.clarifications.show', $clarification, absolute: false),
            'clarification',
            $clarification->id,
        );

        return back()->with('status', 'Jawaban klarifikasi berhasil dikirim.');
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

        return back()->with('status', 'Lampiran klarifikasi berhasil ditambahkan.');
    }

    public function markAnswered(Request $request, Clarification $clarification): RedirectResponse
    {
        $this->authorizeClarification($request, $clarification);
        $this->ensureOpenThread($clarification);

        $clarification->update(['status' => 'dijawab']);
        Notification::sendNotification(
            $clarification->dibuka_oleh,
            'klarifikasi_dijawab',
            'Klarifikasi Dijawab',
            'Auditee menandai klarifikasi sudah dijawab.',
            route('auditor.clarifications.show', $clarification, absolute: false),
            'clarification',
            $clarification->id,
        );

        return back()->with('status', 'Klarifikasi ditandai sudah dijawab.');
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
            ->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->user()->unit_id));
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
        abort_if($clarification->status === 'selesai', 403, 'Klarifikasi sudah selesai dan tidak dapat ditambah dari sisi auditee.');
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

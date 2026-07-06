<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\Visit;
use App\Models\VisitAttachment;
use App\Support\SimplePdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $assignments = $this->assignmentQuery($request)
            ->with(['auditPeriod', 'unit', 'leadAuditor', 'auditors', 'visit'])
            ->paginate(10);

        return view('auditee.visits.index', [
            'assignments' => $assignments,
            'statusOptions' => Visit::statusOptions(),
            'tipeOptions' => Visit::tipeOptions(),
        ]);
    }

    public function show(Request $request, Visit $visit): View
    {
        $this->authorizeVisit($request, $visit);
        $visit->load(['assignment.auditPeriod', 'assignment.unit', 'assignment.leadAuditor', 'assignment.auditors', 'participants', 'attachments.uploader']);

        return view('auditee.visits.show', [
            'visit' => $visit,
            'statusOptions' => Visit::statusOptions(),
            'tipeOptions' => Visit::tipeOptions(),
        ]);
    }

    public function confirm(Request $request, Visit $visit): RedirectResponse
    {
        $this->authorizeVisit($request, $visit);

        $visit->update([
            'konfirmasi_auditee' => true,
            'waktu_konfirmasi_auditee' => now(),
            'status' => $visit->status === 'selesai' ? 'berita_acara_disetujui' : $visit->status,
        ]);

        return back()->with('status', 'Kehadiran visitasi berhasil dikonfirmasi.');
    }

    public function storeAttachment(Request $request, Visit $visit): RedirectResponse
    {
        $this->authorizeVisit($request, $visit);

        $payload = $this->validatedAttachment($request);
        $file = $request->file('file');

        if ($payload['tipe_sumber'] === 'file') {
            $payload['path_file'] = $file->store('visit-attachments', 'public');
            $payload['url_tautan'] = null;
        }

        $visit->attachments()->create([
            ...Arr::except($payload, ['file']),
            'diunggah_oleh' => $request->user()->id,
        ]);

        return back()->with('status', 'Dokumen tambahan visitasi berhasil diunggah.');
    }

    public function minutes(Request $request, Visit $visit): Response
    {
        $this->authorizeVisit($request, $visit);
        abort_unless(in_array($visit->status, ['selesai', 'berita_acara_disetujui'], true), 404);

        return $this->minutesResponse($visit);
    }

    public function downloadAttachment(Request $request, VisitAttachment $attachment): BinaryFileResponse
    {
        $this->authorizeVisit($request, $attachment->visit);
        abort_unless($attachment->path_file && Storage::disk('public')->exists($attachment->path_file), 404);

        return response()->download(Storage::disk('public')->path($attachment->path_file), $attachment->nama_file);
    }

    private function assignmentQuery(Request $request)
    {
        return AuditAssignment::query()
            ->where('status', 'aktif')
            ->where('unit_id', $request->user()->unit_id)
            ->whereHas('auditPeriod', fn ($query) => $query->where('status', 'aktif'))
            ->latest('id');
    }

    private function authorizeVisit(Request $request, Visit $visit): void
    {
        $visit->loadMissing('assignment');
        abort_unless((int) $visit->assignment->unit_id === (int) $request->user()->unit_id, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAttachment(Request $request): array
    {
        return $request->validate([
            'nama_file' => ['required', 'string', 'max:255'],
            'tipe_sumber' => ['required', 'in:file,tautan'],
            'file' => ['required_if:tipe_sumber,file', 'nullable', 'file', 'mimes:'.implode(',', allowedUploadExtensions()), 'max:'.maxUploadKilobytes()],
            'url_tautan' => ['required_if:tipe_sumber,tautan', 'nullable', 'url', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);
    }

    private function minutesResponse(Visit $visit): Response
    {
        $visit->loadMissing(['assignment.auditPeriod', 'assignment.unit', 'assignment.leadAuditor', 'participants']);

        $participants = $visit->participants
            ->map(fn ($participant, int $index): string => ($index + 1).'. '.$participant->nama_peserta.' - '.($participant->jabatan ?: '-').' ('.$participant->tipe.')')
            ->all();

        $lines = [
            'BERITA ACARA VISITASI AUDIT MUTU INTERNAL',
            '',
            'Periode Audit : '.$visit->assignment->auditPeriod->nama,
            'Unit Auditee : '.$visit->assignment->unit->kode.' - '.$visit->assignment->unit->nama,
            'Tanggal Visitasi : '.$visit->tanggal->format('d/m/Y'),
            'Waktu : '.($visit->waktu_mulai ?: '-').' s.d. '.($visit->waktu_selesai ?: '-'),
            'Tipe : '.(Visit::tipeOptions()[$visit->tipe] ?? $visit->tipe),
            'Lokasi/Tautan : '.($visit->lokasi_atau_tautan ?: '-'),
            '',
            'Daftar Peserta:',
            ...($participants ?: ['-']),
            '',
            'Catatan Wawancara:',
            $visit->catatan_wawancara ?: '-',
            '',
            'Kesimpulan:',
            $visit->kesimpulan ?: '-',
            '',
            'Lead Auditor : '.$visit->assignment->leadAuditor->name,
            'Konfirmasi Auditee : '.($visit->konfirmasi_auditee ? 'Sudah dikonfirmasi pada '.$visit->waktu_konfirmasi_auditee?->format('d/m/Y H:i') : 'Belum dikonfirmasi'),
        ];

        return response(SimplePdf::document($lines, reportPrintSettings()), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="berita-acara-visitasi-'.$visit->id.'.pdf"',
        ]);
    }
}

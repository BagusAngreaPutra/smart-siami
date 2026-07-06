<?php

namespace App\Http\Controllers\Auditor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\Notification;
use App\Models\User;
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

        return view('auditor.visits.index', [
            'assignments' => $assignments,
            'statusOptions' => Visit::statusOptions(),
            'tipeOptions' => Visit::tipeOptions(),
        ]);
    }

    public function show(Request $request, AuditAssignment $assignment): View
    {
        $this->authorizeAssignment($request, $assignment);

        $assignment->load(['auditPeriod', 'unit', 'leadAuditor', 'auditors', 'visit.participants', 'visit.attachments.uploader']);

        return view('auditor.visits.show', [
            'assignment' => $assignment,
            'visit' => $assignment->visit,
            'statusOptions' => Visit::statusOptions(),
            'tipeOptions' => Visit::tipeOptions(),
            'participantTypes' => $this->participantTypes(),
        ]);
    }

    public function save(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);

        $payload = $request->validate([
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['nullable', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:waktu_mulai'],
            'tipe' => ['required', 'in:lapangan,daring'],
            'lokasi_atau_tautan' => ['nullable', 'string'],
            'agenda' => ['nullable', 'string'],
            'catatan_wawancara' => ['nullable', 'string'],
            'catatan_observasi' => ['nullable', 'string'],
            'kesimpulan' => ['nullable', 'string'],
        ]);

        $visit = $assignment->visit;
        $wasScheduled = (bool) $visit;

        if ($visit && $visit->status === 'berita_acara_disetujui') {
            return back()->with('warning', 'Visitasi yang berita acaranya sudah disetujui tidak dapat diubah.');
        }

        $visit = Visit::query()->updateOrCreate(
            ['assignment_id' => $assignment->id],
            [
                ...$payload,
                'status' => $visit && $visit->status !== 'belum_dijadwalkan' ? $visit->status : 'terjadwal',
            ],
        );

        $assignment->update(['jadwal_visitasi' => $visit->tanggal]);
        $notificationCount = $this->notifyAuditees($visit, 'jadwal');
        $redirect = back()->with('status', $wasScheduled ? 'Visitasi berhasil diperbarui.' : 'Visitasi berhasil dijadwalkan.');

        return $notificationCount > 0
            ? $redirect
            : $redirect->with('warning', 'Jadwal tersimpan, tetapi tidak ada akun Auditee aktif pada unit ini untuk menerima notifikasi.');
    }

    public function addParticipant(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $visit = $this->requireVisit($assignment);

        $payload = $request->validate([
            'nama_peserta' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'tipe' => ['required', 'in:auditor,auditee,lainnya'],
        ]);

        $visit->participants()->create($payload);

        return back()->with('status', 'Peserta visitasi berhasil ditambahkan.');
    }

    public function storeAttachment(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $visit = $this->requireVisit($assignment);
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

        return back()->with('status', 'Lampiran visitasi berhasil ditambahkan.');
    }

    public function finish(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $visit = $this->requireVisit($assignment);

        if ($visit->tanggal->isFuture()) {
            return back()->with('warning', 'Visitasi baru bisa ditandai selesai setelah tanggal visitasi tiba.');
        }

        if ($visit->status === 'berita_acara_disetujui') {
            return back()->with('warning', 'Berita acara sudah disetujui auditee.');
        }

        $visit->update(['status' => 'selesai']);

        return back()->with('status', 'Visitasi ditandai selesai.');
    }

    public function sendMinutes(Request $request, AuditAssignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $visit = $this->requireVisit($assignment);

        if ($visit->status !== 'berita_acara_disetujui') {
            $visit->update(['status' => 'selesai']);
        }

        $notificationCount = $this->notifyAuditees($visit, 'berita_acara');
        $redirect = back()->with('status', 'Berita acara dikirim ke auditee untuk konfirmasi.');

        return $notificationCount > 0
            ? $redirect
            : $redirect->with('warning', 'Berita acara tersimpan, tetapi tidak ada akun Auditee aktif pada unit ini untuk menerima notifikasi.');
    }

    public function minutes(Request $request, AuditAssignment $assignment): Response
    {
        $this->authorizeAssignment($request, $assignment);
        $visit = $this->requireVisit($assignment);

        return $this->minutesResponse($visit);
    }

    public function downloadAttachment(Request $request, VisitAttachment $attachment): BinaryFileResponse
    {
        $this->authorizeAssignment($request, $attachment->visit->assignment);
        abort_unless($attachment->path_file && Storage::disk('public')->exists($attachment->path_file), 404);

        return response()->download(Storage::disk('public')->path($attachment->path_file), $attachment->nama_file);
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
        abort_unless($this->assignmentQuery($request)->whereKey($assignment->id)->exists(), 403);
    }

    private function requireVisit(AuditAssignment $assignment): Visit
    {
        $visit = $assignment->visit()->with(['assignment.auditPeriod', 'assignment.unit', 'assignment.leadAuditor', 'assignment.auditors', 'participants'])->first();

        abort_unless($visit, 404, 'Jadwal visitasi belum dibuat.');

        return $visit;
    }

    private function notifyAuditees(Visit $visit, string $event): int
    {
        $visit->loadMissing(['assignment.unit', 'assignment.auditPeriod']);

        $isMinutes = in_array($event, ['minutes', 'berita_acara'], true);
        $unitName = $visit->assignment->unit->nama;
        $periodName = $visit->assignment->auditPeriod->nama;
        $date = $visit->tanggal?->format('d/m/Y') ?? '-';
        $time = trim(($visit->waktu_mulai ?: '-').' - '.($visit->waktu_selesai ?: '-'));
        $typeLabel = Visit::tipeOptions()[$visit->tipe] ?? ucfirst($visit->tipe);
        $location = $visit->lokasi_atau_tautan ?: '-';
        $agenda = $visit->agenda ?: '-';
        $type = $isMinutes ? 'berita_acara_dikirim' : 'visitasi_dijadwalkan';
        $title = $isMinutes ? 'Berita Acara Visitasi' : 'Jadwal Visitasi';
        $message = $isMinutes
            ? "Berita acara visitasi untuk unit {$unitName} pada periode {$periodName} telah dikirim untuk konfirmasi."
            : "Auditor telah menetapkan atau memperbarui jadwal visitasi untuk unit {$unitName} pada periode {$periodName}. Jadwal: {$date}, pukul {$time}. Tipe: {$typeLabel}. Lokasi/Tautan: {$location}. Agenda: {$agenda}.";

        $auditees = User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('unit_id', $visit->assignment->unit_id)
            ->where('is_active', true)
            ->get();

        $auditees->each(function (User $user) use ($visit, $event, $type, $title, $message): void {
                Notification::sendNotification(
                    $user->id,
                    $type,
                    $title,
                    $message,
                    route('auditee.visit-schedules.show', $visit, absolute: false),
                    'visit',
                    $visit->id,
                );
            });

        return $auditees->count();
    }

    /**
     * @return array<string, string>
     */
    private function participantTypes(): array
    {
        return [
            'auditor' => 'Auditor',
            'auditee' => 'Auditee',
            'lainnya' => 'Lainnya',
        ];
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
            'Catatan Observasi:',
            $visit->catatan_observasi ?: '-',
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

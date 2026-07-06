<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\Evidence;
use App\Models\Instrument;
use App\Models\SelfAssessment;
use App\Models\Standard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvidenceController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->unitEvidenceQuery($request)
            ->with(['selfAssessment.instrument.standard', 'uploader'])
            ->latest();

        if ($request->filled('standard_id')) {
            $instrumentIds = Instrument::query()
                ->where('standard_id', $request->integer('standard_id'))
                ->pluck('id')
                ->map(fn (int $id): string => (string) $id)
                ->all();

            if ($instrumentIds === []) {
                $query->whereRaw('1 = 0');
            }

            $query->where(function ($query) use ($instrumentIds): void {
                foreach ($instrumentIds as $instrumentId) {
                    $query->orWhereJsonContains('instrument_ids', (int) $instrumentId);
                }
            });
        }

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->string('status_verifikasi')->toString());
        }

        if ($request->filled('tahun_dokumen')) {
            $query->where('tahun_dokumen', $request->integer('tahun_dokumen'));
        }

        return view('auditee.evidences.index', [
            'evidences' => $query->paginate(10)->withQueryString(),
            'standards' => Standard::query()->orderBy('urutan')->get(),
            'statusOptions' => Evidence::statusVerifikasiOptions(),
            'years' => $this->unitEvidenceQuery($request)->whereNotNull('tahun_dokumen')->distinct()->orderByDesc('tahun_dokumen')->pluck('tahun_dokumen'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('auditee.evidences.form', [
            'assignments' => $this->assignmentQuery($request)->get(),
            'instruments' => Instrument::query()->with('standard')->where('is_active', true)->orderBy('standard_id')->orderBy('urutan')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'assignment_id' => ['required', 'integer', 'exists:audit_assignments,id'],
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'jenis_dokumen' => ['nullable', 'string', 'max:255'],
            'instrument_ids' => ['required', 'array', 'min:1'],
            'instrument_ids.*' => ['integer', 'exists:instruments,id'],
            'tipe_sumber' => ['required', 'in:file,tautan'],
            'file' => ['required_if:tipe_sumber,file', 'nullable', 'file', 'mimes:'.implode(',', allowedUploadExtensions()), 'max:'.maxUploadKilobytes()],
            'url_tautan' => ['required_if:tipe_sumber,tautan', 'nullable', 'url', 'max:255'],
            'tahun_dokumen' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $assignment = $this->assignmentQuery($request)->whereKey($payload['assignment_id'])->firstOrFail();
        $this->ensureCanUpload($assignment);
        $this->ensureSelfAssessments($assignment);

        $instruments = Instrument::query()
            ->whereIn('id', $payload['instrument_ids'])
            ->orderBy('kode')
            ->get();
        $firstAssessment = SelfAssessment::query()
            ->where('assignment_id', $assignment->id)
            ->where('instrument_id', $instruments->first()->id)
            ->first();
        $file = $request->file('file');

        if ($payload['tipe_sumber'] === 'file') {
            $payload['path_file'] = $file->store('evidences', 'public');
            $payload['ukuran_file'] = $file->getSize();
            $payload['url_tautan'] = null;
        }

        Evidence::query()->create([
            ...Arr::except($payload, ['assignment_id', 'file']),
            'self_assessment_id' => $firstAssessment?->id,
            'uploaded_by' => $request->user()->id,
            'instrumen_terkait' => $instruments->pluck('kode')->join(', '),
            'instrument_ids' => $instruments->pluck('id')->values()->all(),
        ]);

        return redirect()->route('auditee.documents')->with('status', 'Bukti dokumen berhasil diunggah.');
    }

    public function destroy(Request $request, Evidence $evidence): RedirectResponse
    {
        $this->authorizeEvidence($request, $evidence);

        if ($evidence->status_verifikasi !== 'belum_diperiksa') {
            return back()->with('warning', 'Bukti yang sudah diperiksa auditor tidak dapat dihapus.');
        }

        if ($evidence->path_file) {
            Storage::disk('public')->delete($evidence->path_file);
        }

        $evidence->delete();

        return back()->with('status', 'Bukti dokumen berhasil dihapus.');
    }

    public function download(Request $request, Evidence $evidence): BinaryFileResponse
    {
        $this->authorizeEvidence($request, $evidence);
        abort_unless($evidence->path_file && Storage::disk('public')->exists($evidence->path_file), 404);

        return response()->download(Storage::disk('public')->path($evidence->path_file), $evidence->nama_dokumen);
    }

    public function preview(Request $request, Evidence $evidence): BinaryFileResponse
    {
        $this->authorizeEvidence($request, $evidence);
        abort_unless($evidence->path_file && Storage::disk('public')->exists($evidence->path_file), 404);

        return response()->file(Storage::disk('public')->path($evidence->path_file));
    }

    private function assignmentQuery(Request $request)
    {
        return AuditAssignment::query()
            ->with(['auditPeriod', 'unit'])
            ->where('unit_id', $request->user()->unit_id)
            ->where('status', 'aktif');
    }

    private function unitEvidenceQuery(Request $request)
    {
        return Evidence::query()
            ->whereHas('uploader', fn ($query) => $query->where('unit_id', $request->user()->unit_id));
    }

    private function authorizeEvidence(Request $request, Evidence $evidence): void
    {
        abort_unless($evidence->uploader?->unit_id === $request->user()->unit_id, 403);
    }

    private function ensureCanUpload(AuditAssignment $assignment): void
    {
        abort_unless(
            $assignment->auditPeriod->status === 'aktif'
            && now()->toDateString() <= $assignment->auditPeriod->batas_evaluasi_diri->toDateString(),
            403,
            'Bukti tidak dapat diunggah karena periode sudah ditutup atau melewati batas evaluasi diri.'
        );
    }

    private function ensureSelfAssessments(AuditAssignment $assignment): void
    {
        foreach (Instrument::query()->where('is_active', true)->get() as $instrument) {
            SelfAssessment::query()->firstOrCreate(
                ['assignment_id' => $assignment->id, 'instrument_id' => $instrument->id],
                ['target' => $instrument->target_kriteria, 'status' => 'belum_diisi'],
            );
        }
    }
}

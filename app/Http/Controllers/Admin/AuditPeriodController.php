<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditPeriod;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditPeriodController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditPeriod::query()->with('creator')->latest('tanggal_mulai')->latest('id');

        if ($request->filled('tahun_akademik')) {
            $query->where('tahun_akademik', $request->string('tahun_akademik')->toString());
        }

        if ($request->filled('jenis_audit')) {
            $query->where('jenis_audit', $request->string('jenis_audit')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('admin.audit-periods.index', [
            'periods' => $query->paginate(10)->withQueryString(),
            'years' => AuditPeriod::query()->select('tahun_akademik')->distinct()->orderByDesc('tahun_akademik')->pluck('tahun_akademik'),
            'jenisAuditOptions' => AuditPeriod::jenisAuditOptions(),
            'statusOptions' => AuditPeriod::statusOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.audit-periods.form', [
            'period' => new AuditPeriod([
                'jenis_audit' => 'reguler',
                'status' => 'draft',
            ]),
            'jenisAuditOptions' => AuditPeriod::jenisAuditOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $period = AuditPeriod::query()->create([
            ...$this->validated($request),
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.periods.show', $period)->with('status', 'Periode audit berhasil ditambahkan sebagai draft.');
    }

    public function show(AuditPeriod $period): View
    {
        return view('admin.audit-periods.show', [
            'period' => $period->load('creator'),
            'summary' => $period->summary(),
            'jenisAuditOptions' => AuditPeriod::jenisAuditOptions(),
            'statusOptions' => AuditPeriod::statusOptions(),
        ]);
    }

    public function edit(AuditPeriod $period): View|RedirectResponse
    {
        if (! $period->canBeEdited()) {
            return redirect()->route('admin.periods.show', $period)->with('warning', 'Periode yang sudah diarsipkan tidak dapat diedit.');
        }

        return view('admin.audit-periods.form', [
            'period' => $period,
            'jenisAuditOptions' => AuditPeriod::jenisAuditOptions(),
        ]);
    }

    public function update(Request $request, AuditPeriod $period): RedirectResponse
    {
        if (! $period->canBeEdited()) {
            return redirect()->route('admin.periods.show', $period)->with('warning', 'Periode yang sudah diarsipkan tidak dapat diedit.');
        }

        $period->update($this->validated($request));

        return redirect()->route('admin.periods.show', $period)->with('status', 'Periode audit berhasil diperbarui.');
    }

    public function duplicate(Request $request, AuditPeriod $period): RedirectResponse
    {
        $copy = $period->replicate([
            'status',
            'created_by',
            'created_at',
            'updated_at',
        ]);
        $copy->nama = $period->nama.' - Salinan';
        $copy->status = 'draft';
        $copy->created_by = $request->user()->id;
        $copy->save();

        return redirect()->route('admin.periods.edit', $copy)->with('status', 'Periode berhasil diduplikasi sebagai draft baru.');
    }

    public function activate(AuditPeriod $period): RedirectResponse
    {
        if (! $period->canActivate()) {
            return back()->with('warning', 'Hanya periode draft yang dapat diaktifkan.');
        }

        $activePeriod = AuditPeriod::query()
            ->where('status', 'aktif')
            ->whereKeyNot($period->id)
            ->first();

        if ($activePeriod) {
            return back()->with('warning', "Tidak dapat mengaktifkan periode ini karena {$activePeriod->nama} masih berstatus aktif.");
        }

        $period->update(['status' => 'aktif']);

        return back()->with('status', 'Periode audit berhasil diaktifkan.');
    }

    public function close(Request $request, AuditPeriod $period): RedirectResponse
    {
        if (! $period->canClose()) {
            return back()->with('warning', 'Hanya periode aktif yang dapat ditutup.');
        }

        if ($period->hasOpenFindings() && ! $request->boolean('force_close')) {
            return back()->with('warning', 'Masih ada temuan yang belum ditutup. Centang konfirmasi untuk memaksa tutup periode.');
        }

        $period->update(['status' => 'ditutup']);

        return back()->with('status', 'Periode audit berhasil ditutup.');
    }

    public function archive(AuditPeriod $period): RedirectResponse
    {
        if (! $period->canArchive()) {
            return back()->with('warning', 'Hanya periode berstatus ditutup yang dapat diarsipkan.');
        }

        $period->update(['status' => 'diarsipkan']);

        return back()->with('status', 'Periode audit berhasil diarsipkan.');
    }

    public function notifyOpening(AuditPeriod $period): RedirectResponse
    {
        if ($period->status !== 'aktif') {
            return back()->with('warning', 'Notifikasi pembukaan hanya dapat dikirim untuk periode yang aktif.');
        }

        $recipients = User::query()
            ->where('is_active', true)
            ->whereIn('role', [UserRole::Auditor->value, UserRole::Auditee->value])
            ->get();

        DB::transaction(function () use ($recipients, $period): void {
            $recipients->each(function (User $user) use ($period): void {
                Notification::sendNotification(
                    $user->id,
                    'periode_dibuka',
                    'Periode Audit Dibuka',
                    "Periode audit {$period->nama} telah dibuka.",
                    route('dashboard', absolute: false),
                    'audit_period',
                    $period->id,
                );
            });
        });

        return back()->with('status', "Notifikasi pembukaan dikirim ke {$recipients->count()} auditor/auditee.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tahun_akademik' => ['required', 'string', 'max:50'],
            'jenis_audit' => ['required', 'in:reguler,akademik,nonakademik,tindak_lanjut,khusus'],
            'tanggal_mulai' => ['required', 'date'],
            'batas_evaluasi_diri' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'batas_desk_evaluation' => ['required', 'date', 'after_or_equal:batas_evaluasi_diri'],
            'visitasi_mulai' => ['nullable', 'date', 'after_or_equal:batas_desk_evaluation'],
            'visitasi_selesai' => ['nullable', 'date', 'after_or_equal:visitasi_mulai'],
            'batas_tindak_lanjut' => ['required', 'date', 'after_or_equal:batas_desk_evaluation'],
            'catatan' => ['nullable', 'string'],
        ]);
    }
}

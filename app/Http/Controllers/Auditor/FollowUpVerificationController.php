<?php

namespace App\Http\Controllers\Auditor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditPeriod;
use App\Models\Evidence;
use App\Models\FollowUp;
use App\Models\FollowUpVerification;
use App\Models\Notification;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FollowUpVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->followUpQuery($request)
            ->with(['finding.standard', 'assignment.unit', 'assignment.auditPeriod'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        } else {
            $query->where('status', 'diajukan');
        }

        if ($request->filled('unit_id')) {
            $query->whereHas('assignment', fn ($query) => $query->where('unit_id', $request->integer('unit_id')));
        }

        if ($request->filled('audit_period_id')) {
            $query->whereHas('assignment', fn ($query) => $query->where('audit_period_id', $request->integer('audit_period_id')));
        }

        return view('auditor.follow-up-verifications.index', [
            'followUps' => $query->paginate(10)->withQueryString(),
            'statusOptions' => FollowUp::statusOptions(),
            'units' => Unit::query()->whereHas('auditAssignments', fn ($query) => $this->scopeAssignmentToAuditor($request, $query))->orderBy('nama')->get(),
            'periods' => AuditPeriod::query()->whereHas('assignments', fn ($query) => $this->scopeAssignmentToAuditor($request, $query))->latest('id')->get(),
        ]);
    }

    public function show(Request $request, FollowUp $followUp): View
    {
        $this->authorizeFollowUp($request, $followUp);

        $followUp->load([
            'finding.standard',
            'finding.instrument',
            'assignment.unit',
            'assignment.auditPeriod',
            'creator',
            'evidences.uploader',
            'verifications.verifier',
        ]);

        return view('auditor.follow-up-verifications.show', [
            'followUp' => $followUp,
            'finding' => $followUp->finding,
            'statusOptions' => FollowUp::statusOptions(),
            'progresOptions' => FollowUp::progresOptions(),
            'keputusanOptions' => FollowUpVerification::keputusanOptions(),
        ]);
    }

    public function verify(Request $request, FollowUp $followUp): RedirectResponse
    {
        $this->authorizeFollowUp($request, $followUp);

        if ($followUp->status !== 'diajukan') {
            return back()->with('warning', 'Hanya tindak lanjut berstatus diajukan yang dapat diverifikasi.');
        }

        $payload = $request->validate([
            'keputusan' => ['required', Rule::in(array_keys(FollowUpVerification::keputusanOptions()))],
            'catatan_verifikasi' => ['nullable', 'required_if:keputusan,perlu_perbaikan,ditolak', 'string'],
        ]);

        $verification = DB::transaction(function () use ($followUp, $payload, $request): FollowUpVerification {
            $verification = $followUp->verifications()->create([
                'verifikator_id' => $request->user()->id,
                'keputusan' => $payload['keputusan'],
                'catatan_verifikasi' => $payload['catatan_verifikasi'] ?? null,
                'waktu_verifikasi' => now(),
            ]);

            if ($payload['keputusan'] === 'disetujui') {
                $followUp->update(['status' => 'disetujui']);
                $this->moveFindingStatus($followUp, 'ditutup', $request->user()->id, 'Tindak lanjut disetujui auditor.');
            } else {
                $followUp->update(['status' => 'perlu_perbaikan']);
                $this->moveFindingStatus($followUp, 'dalam_tindak_lanjut', $request->user()->id, 'Tindak lanjut perlu perbaikan.');
            }

            return $verification;
        });

        $this->notifyAuditees($verification);

        return back()->with('status', 'Keputusan verifikasi tindak lanjut berhasil disimpan.');
    }

    public function downloadEvidence(Request $request, Evidence $evidence): BinaryFileResponse
    {
        $followUp = $evidence->followUp;
        abort_unless($followUp, 404);
        $this->authorizeFollowUp($request, $followUp);
        abort_unless($evidence->path_file && Storage::disk('public')->exists($evidence->path_file), 404);

        return response()->download(Storage::disk('public')->path($evidence->path_file), $evidence->nama_dokumen);
    }

    private function followUpQuery(Request $request)
    {
        return FollowUp::query()
            ->whereHas('assignment', fn ($query) => $this->scopeAssignmentToAuditor($request, $query));
    }

    private function scopeAssignmentToAuditor(Request $request, $query): void
    {
        $query->where(function ($query) use ($request): void {
            $query->where('lead_auditor_id', $request->user()->id)
                ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $request->user()->id));
        });
    }

    private function authorizeFollowUp(Request $request, FollowUp $followUp): void
    {
        abort_unless($this->followUpQuery($request)->whereKey($followUp->id)->exists(), 403);
    }

    private function moveFindingStatus(FollowUp $followUp, string $status, int $userId, string $note): void
    {
        $finding = $followUp->finding;
        $oldStatus = $finding->status;
        $finding->update(['status' => $status]);
        $finding->histories()->create([
            'dari_status' => $oldStatus,
            'ke_status' => $status,
            'catatan' => $note,
            'changed_by' => $userId,
        ]);
    }

    private function notifyAuditees(FollowUpVerification $verification): void
    {
        $verification->loadMissing('followUp.assignment');

        User::query()
            ->where('role', UserRole::Auditee->value)
            ->where('unit_id', $verification->followUp->assignment->unit_id)
            ->where('is_active', true)
            ->get()
            ->each(function (User $user) use ($verification): void {
                $approved = $verification->keputusan === 'disetujui';
                Notification::sendNotification(
                    $user->id,
                    'tindak_lanjut_diverifikasi',
                    'Tindak Lanjut Diverifikasi',
                    $approved
                        ? 'Tindak lanjut Anda telah disetujui auditor.'
                        : 'Tindak lanjut Anda perlu perbaikan. '.$verification->catatan_verifikasi,
                    route('auditee.findings-followups.show', $verification->followUp->finding, absolute: false),
                    'follow_up_verification',
                    $verification->id,
                );
            });
    }
}

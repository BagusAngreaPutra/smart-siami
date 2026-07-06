<?php

namespace App\Http\Controllers;

use App\Models\AuditAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentPortalController extends Controller
{
    public function auditorDashboard(Request $request): View
    {
        return view('dashboards.assignments', [
            'title' => 'Dashboard Auditor',
            'assignments' => $this->auditorAssignments($request)->limit(5)->get(),
            'emptyMessage' => 'Belum ada penugasan audit aktif untuk Anda.',
        ]);
    }

    public function auditorTasks(Request $request): View
    {
        return view('portal.assignments', [
            'title' => 'Tugas Audit',
            'assignments' => $this->auditorAssignments($request)->paginate(10),
            'emptyMessage' => 'Belum ada tugas audit aktif untuk Anda.',
        ]);
    }

    public function auditeeDashboard(Request $request): View
    {
        return view('dashboards.assignments', [
            'title' => 'Dashboard Auditee',
            'assignments' => $this->auditeeAssignments($request)->limit(5)->get(),
            'emptyMessage' => 'Belum ada penugasan audit aktif untuk unit Anda.',
        ]);
    }

    private function auditorAssignments(Request $request)
    {
        $user = $request->user();

        return AuditAssignment::query()
            ->with(['auditPeriod', 'unit', 'leadAuditor', 'auditors', 'visit'])
            ->withCount([
                'selfAssessments as self_total',
                'selfAssessments as self_submitted' => fn ($query) => $query->whereIn('status', ['dikirim', 'final']),
                'evaluations as desk_total',
                'evaluations as desk_checked' => fn ($query) => $query->where('status_pemeriksaan', '!=', 'belum_dimulai'),
                'findings as active_findings_count' => fn ($query) => $query->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat']),
            ])
            ->where('status', 'aktif')
            ->whereHas('auditPeriod', fn ($query) => $query->where('status', 'aktif'))
            ->where(function ($query) use ($user): void {
                $query->where('lead_auditor_id', $user->id)
                    ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $user->id));
            })
            ->latest('id');
    }

    private function auditeeAssignments(Request $request)
    {
        return AuditAssignment::query()
            ->with(['auditPeriod', 'unit', 'leadAuditor', 'auditors'])
            ->where('status', 'aktif')
            ->where('unit_id', $request->user()->unit_id)
            ->whereHas('auditPeriod', fn ($query) => $query->where('status', 'aktif'))
            ->latest('id');
    }
}

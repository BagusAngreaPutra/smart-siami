<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditAssignment;
use App\Models\Finding;
use App\Models\FollowUp;
use App\Models\SelfAssessment;
use App\Models\Unit;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $unit = $user->unit;

        $assignment = $unit
            ? AuditAssignment::query()
                ->with([
                    'auditPeriod',
                    'leadAuditor',
                    'auditors',
                    'visit',
                ])
                ->where('unit_id', $unit->id)
                ->where('status', 'aktif')
                ->whereHas('auditPeriod', fn ($query) => $query->where('status', 'aktif'))
                ->latest()
                ->first()
            : null;

        $assignmentId = $assignment?->id;
        $assessmentCounts = $assignmentId
            ? SelfAssessment::query()
                ->where('assignment_id', $assignmentId)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();
        $totalAssessments = (int) $assessmentCounts->sum();
        $submittedAssessments = (int) $assessmentCounts->only(['dikirim', 'final'])->sum();
        $selfAssessmentProgress = $totalAssessments > 0
            ? (int) round(($submittedAssessments / $totalAssessments) * 100)
            : 0;

        $findingCounts = $assignmentId
            ? Finding::query()
                ->where('assignment_id', $assignmentId)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        $latestFindings = $assignmentId
            ? Finding::query()
                ->with('latestFollowUp')
                ->where('assignment_id', $assignmentId)
                ->whereIn('status', ['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat'])
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $pendingFollowUps = $assignmentId
            ? FollowUp::query()
                ->where('assignment_id', $assignmentId)
                ->whereIn('status', ['draft', 'diajukan', 'perlu_perbaikan'])
                ->count()
            : 0;

        return view('auditee.unit-profile.show', [
            'unit' => $unit,
            'profileUser' => $user,
            'assignment' => $assignment,
            'jenisUnitOptions' => Unit::jenisUnitOptions(),
            'assessmentStatusOptions' => SelfAssessment::statusOptions(),
            'findingStatusOptions' => Finding::statusOptions(),
            'followUpStatusOptions' => FollowUp::statusOptions(),
            'visitStatusOptions' => Visit::statusOptions(),
            'assessmentCounts' => $assessmentCounts,
            'findingCounts' => $findingCounts,
            'latestFindings' => $latestFindings,
            'selfAssessmentProgress' => $selfAssessmentProgress,
            'pendingFollowUps' => $pendingFollowUps,
        ]);
    }
}

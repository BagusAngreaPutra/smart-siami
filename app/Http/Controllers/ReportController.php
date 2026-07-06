<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Evaluation;
use App\Models\Evidence;
use App\Models\Finding;
use App\Models\FollowUp;
use App\Models\Unit;
use App\Support\ExcelXml;
use App\Support\AuditVisuals;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $assignments = $this->assignmentQuery($request)
            ->with(['auditPeriod', 'unit', 'leadAuditor', 'visit'])
            ->latest('id')
            ->get();

        return view('reports.index', [
            'scope' => $scope,
            'title' => match ($scope) {
                'admin' => 'Laporan',
                'auditor' => 'Laporan Saya',
                default => 'Laporan Unit',
            },
            'assignments' => $assignments,
            'periodOptions' => AuditPeriod::query()
                ->whereHas('assignments', fn ($query) => $this->scopeAssignmentQuery($request, $query))
                ->orderByDesc('tanggal_mulai')
                ->get(),
            'unitOptions' => Unit::query()
                ->whereHas('auditAssignments', fn ($query) => $this->scopeAssignmentQuery($request, $query))
                ->orderBy('kode')
                ->get(),
            'reportTypes' => $this->reportTypes($scope),
        ]);
    }

    public function preview(Request $request, string $report, AuditAssignment $assignment): View
    {
        $this->authorizeReport($request, $report, $assignment);

        return view('reports.preview', [
            'report' => $this->buildAssignmentReport($report, $assignment),
            'printSettings' => reportPrintSettings(),
        ]);
    }

    public function download(Request $request, string $report, AuditAssignment $assignment): Response
    {
        $this->authorizeReport($request, $report, $assignment);
        $payload = $this->buildAssignmentReport($report, $assignment);

        $printSettings = reportPrintSettings();

        return response(SimplePdf::report($payload, $printSettings), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename($report, $assignment, 'pdf').'"',
        ]);
    }

    public function excel(Request $request, string $report, AuditAssignment $assignment): StreamedResponse
    {
        $this->authorizeReport($request, $report, $assignment);
        abort_unless(in_array($report, ['self-assessment', 'findings', 'follow-ups'], true), 404);

        $payload = $this->buildAssignmentReport($report, $assignment);

        return $this->excelFromPayload($this->filename($report, $assignment, 'xls'), $payload);
    }

    public function institutionPreview(Request $request): View
    {
        abort_unless($request->user()->role === UserRole::Admin, 403);

        return view('reports.preview', [
            'report' => $this->buildInstitutionReport($request),
            'printSettings' => reportPrintSettings(),
        ]);
    }

    public function institutionDownload(Request $request): Response
    {
        abort_unless($request->user()->role === UserRole::Admin, 403);
        $payload = $this->buildInstitutionReport($request);

        $printSettings = reportPrintSettings();

        return response(SimplePdf::report($payload, $printSettings), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rekap-audit-institusi.pdf"',
        ]);
    }

    public function institutionExcel(Request $request): StreamedResponse
    {
        abort_unless($request->user()->role === UserRole::Admin, 403);

        return $this->excelFromPayload('rekap-audit-institusi.xls', $this->buildInstitutionReport($request));
    }

    private function buildAssignmentReport(string $report, AuditAssignment $assignment): array
    {
        $assignment->loadMissing([
            'auditPeriod',
            'unit',
            'leadAuditor',
            'auditors',
            'selfAssessments.instrument.standard',
            'selfAssessments.evidences',
            'evaluations.instrument.standard',
            'evaluations.selfAssessment',
            'visit.participants',
            'visit.attachments',
            'findings.standard',
            'findings.instrument',
            'findings.followUps.evidences',
            'findings.followUps.latestVerification',
        ]);

        $standardScores = AuditVisuals::standardScores(
            $assignment->selfAssessments->pluck('instrument.standard')->filter()->unique('id')->sortBy('urutan')->values(),
            $assignment->selfAssessments,
        );

        $base = [
            'institution' => getSetting('nama_institusi', 'SIAMI'),
            'quality_unit' => getSetting('nama_lpm', 'Lembaga Penjaminan Mutu'),
            'subtitle' => $assignment->unit->kode.' - '.$assignment->unit->nama.' | '.$assignment->auditPeriod->nama,
            'meta' => [
                'Unit' => $assignment->unit->nama,
                'Kode Unit' => $assignment->unit->kode,
                'Periode' => $assignment->auditPeriod->nama,
                'Tahun Akademik' => $assignment->auditPeriod->tahun_akademik,
                'Lead Auditor' => $assignment->leadAuditor->name,
            ],
            'visuals' => [
                'readiness' => AuditVisuals::readiness($assignment->selfAssessments, max($assignment->selfAssessments->count(), 1)),
                'radar' => $standardScores,
            ],
        ];

        return match ($report) {
            'self-assessment' => [
                ...$base,
                'title' => 'Rekap Evaluasi Diri Unit',
                'tables' => [$this->selfAssessmentTable($assignment)],
            ],
            'desk-evaluation' => [
                ...$base,
                'title' => 'Lembar Desk Evaluation',
                'tables' => [$this->deskEvaluationTable($assignment)],
            ],
            'visit-minutes' => [
                ...$base,
                'title' => 'Berita Acara Visitasi',
                'tables' => [$this->visitMinutesTable($assignment)],
            ],
            'findings' => [
                ...$base,
                'title' => 'Daftar Temuan Audit',
                'tables' => [$this->findingsTable($assignment)],
            ],
            'follow-ups' => [
                ...$base,
                'title' => 'Rekap Tindak Lanjut',
                'tables' => [$this->followUpsTable($assignment)],
            ],
            'unit-audit' => [
                ...$base,
                'title' => 'Laporan Hasil Audit Unit',
                'tables' => [
                    $this->selfAssessmentTable($assignment, 'Ringkasan Evaluasi Diri'),
                    $this->deskEvaluationTable($assignment, 'Penilaian Auditor'),
                    $this->findingsTable($assignment, 'Daftar Temuan'),
                    $this->followUpsTable($assignment, 'Rekap Tindak Lanjut'),
                ],
            ],
            default => abort(404),
        };
    }

    private function buildInstitutionReport(Request $request): array
    {
        $periodId = $request->integer('audit_period_id') ?: AuditPeriod::query()->where('status', 'aktif')->value('id');
        $assignments = AuditAssignment::query()
            ->with(['auditPeriod', 'unit', 'selfAssessments.instrument.standard', 'evaluations', 'findings.followUps'])
            ->when($periodId, fn ($query) => $query->where('audit_period_id', $periodId))
            ->where('status', 'aktif')
            ->orderBy('unit_id')
            ->get();
        $period = $periodId ? AuditPeriod::query()->find($periodId) : null;
        $standards = $assignments
            ->flatMap(fn (AuditAssignment $assignment) => $assignment->selfAssessments->pluck('instrument.standard'))
            ->filter()
            ->unique('id')
            ->sortBy('urutan')
            ->values();

        return [
            'institution' => getSetting('nama_institusi', 'SIAMI'),
            'quality_unit' => getSetting('nama_lpm', 'Lembaga Penjaminan Mutu'),
            'title' => 'Rekap Audit Institusi',
            'subtitle' => $period?->nama ?? 'Semua Periode',
            'meta' => [
                'Periode' => $period?->nama ?? 'Semua Periode',
                'Jumlah Unit' => (string) $assignments->count(),
            ],
            'visuals' => [
                'readiness' => $assignments->isEmpty()
                    ? 0
                    : (int) round($assignments->avg(fn (AuditAssignment $assignment): int => AuditVisuals::readiness($assignment->selfAssessments, max($assignment->selfAssessments->count(), 1)))),
                'radar' => AuditVisuals::averageStandards($assignments, $standards),
                'heatmap' => [
                    'standards' => $standards,
                    'rows' => AuditVisuals::heatmap($assignments, $standards),
                ],
            ],
            'tables' => [[
                'title' => 'Rekap Semua Unit',
                'headers' => ['Unit', 'Evaluasi Diri', 'Desk Evaluation', 'Observasi', 'Peluang', 'Minor', 'Mayor', 'TL Disetujui', 'TL Tertunda', 'TL Terlambat', 'Kepatuhan'],
                'rows' => $assignments->map(function (AuditAssignment $assignment): array {
                    $findings = $assignment->findings;
                    $followUps = $findings->flatMap(fn (Finding $finding): Collection => $finding->followUps);
                    $selfTotal = $assignment->selfAssessments->count();
                    $selfFinal = $assignment->selfAssessments->where('status', 'final')->count();
                    $evalTotal = $assignment->evaluations->count();
                    $evalFinal = $assignment->evaluations->where('status_pemeriksaan', 'final')->count();
                    $compliance = $findings->count() === 0
                        ? 100
                        : (int) round(($findings->where('status', 'ditutup')->count() / max(1, $findings->count())) * 100);

                    return [
                        $assignment->unit->kode.' - '.$assignment->unit->nama,
                        "{$selfFinal}/{$selfTotal}",
                        "{$evalFinal}/{$evalTotal}",
                        $findings->where('kategori', 'observasi')->count(),
                        $findings->where('kategori', 'peluang_peningkatan')->count(),
                        $findings->where('kategori', 'minor')->count(),
                        $findings->where('kategori', 'mayor')->count(),
                        $followUps->where('status', 'disetujui')->count(),
                        $followUps->whereIn('status', ['draft', 'diajukan', 'perlu_perbaikan'])->count(),
                        $findings->where('status', 'terlambat')->count(),
                        $compliance.'%',
                    ];
                })->all(),
            ]],
        ];
    }

    private function selfAssessmentTable(AuditAssignment $assignment, string $title = 'Evaluasi Diri'): array
    {
        return [
            'title' => $title,
            'headers' => ['Instrumen', 'Pertanyaan', 'Jawaban', 'Realisasi', 'Target', 'Status Bukti', 'Status Jawaban'],
            'rows' => $assignment->selfAssessments
                ->sortBy(fn ($assessment): string => $assessment->instrument->standard->kode.'-'.$assessment->instrument->urutan)
                ->map(fn ($assessment): array => [
                    $assessment->instrument->kode,
                    $assessment->instrument->pertanyaan,
                    $assessment->jawaban_naratif ?: '-',
                    $assessment->realisasi ?: '-',
                    $assessment->target ?: $assessment->instrument->target_kriteria,
                    $assessment->evidences->pluck('status_verifikasi')->unique()->implode(', ') ?: 'belum_diperiksa',
                    $assessment->status,
                ])->all(),
        ];
    }

    private function deskEvaluationTable(AuditAssignment $assignment, string $title = 'Desk Evaluation'): array
    {
        return [
            'title' => $title,
            'headers' => ['Instrumen', 'Jawaban Auditee', 'Skor', 'Status Bukti', 'Catatan Auditor'],
            'rows' => $assignment->evaluations
                ->sortBy(fn (Evaluation $evaluation): string => $evaluation->instrument->standard->kode.'-'.$evaluation->instrument->urutan)
                ->map(fn (Evaluation $evaluation): array => [
                    $evaluation->instrument->kode,
                    $evaluation->selfAssessment?->jawaban_naratif ?: '-',
                    $evaluation->skor ?? '-',
                    Evaluation::statusBuktiOptions()[$evaluation->status_bukti] ?? $evaluation->status_bukti,
                    $evaluation->catatan_auditor ?: '-',
                ])->all(),
        ];
    }

    private function visitMinutesTable(AuditAssignment $assignment): array
    {
        $visit = $assignment->visit;

        return [
            'title' => 'Berita Acara Visitasi',
            'headers' => ['Komponen', 'Isi'],
            'rows' => $visit ? [
                ['Tanggal', $visit->tanggal->format('d/m/Y')],
                ['Waktu', ($visit->waktu_mulai ?: '-').' - '.($visit->waktu_selesai ?: '-')],
                ['Tipe', $visit->tipe],
                ['Lokasi/Tautan', $visit->lokasi_atau_tautan ?: '-'],
                ['Agenda', $visit->agenda ?: '-'],
                ['Peserta', $visit->participants->map(fn ($participant): string => $participant->nama_peserta.' ('.$participant->tipe.')')->implode('; ') ?: '-'],
                ['Catatan Wawancara', $visit->catatan_wawancara ?: '-'],
                ['Catatan Observasi', $visit->catatan_observasi ?: '-'],
                ['Kesimpulan', $visit->kesimpulan ?: '-'],
                ['Konfirmasi Auditee', $visit->konfirmasi_auditee ? 'Sudah dikonfirmasi' : 'Belum dikonfirmasi'],
            ] : [
                ['Status', 'Jadwal visitasi belum tersedia.'],
            ],
        ];
    }

    private function findingsTable(AuditAssignment $assignment, string $title = 'Temuan Audit'): array
    {
        return [
            'title' => $title,
            'headers' => ['Standar', 'Nomor', 'Kategori', 'Kondisi Aktual', 'Kriteria', 'Bukti Objektif', 'Rekomendasi', 'Target', 'Status'],
            'rows' => $assignment->findings
                ->sortBy(fn (Finding $finding): string => $finding->standard->kode.'-'.$finding->nomor_temuan)
                ->map(fn (Finding $finding): array => [
                    $finding->standard->kode,
                    $finding->nomor_temuan ?: 'Draft #'.$finding->id,
                    Finding::kategoriOptions()[$finding->kategori] ?? $finding->kategori,
                    $finding->kondisi_aktual,
                    $finding->kriteria,
                    $finding->bukti_objektif,
                    $finding->rekomendasi_auditor,
                    $finding->target_penyelesaian->format('d/m/Y'),
                    Finding::statusOptions()[$finding->status] ?? $finding->status,
                ])->all(),
        ];
    }

    private function followUpsTable(AuditAssignment $assignment, string $title = 'Tindak Lanjut'): array
    {
        return [
            'title' => $title,
            'headers' => ['Nomor Temuan', 'Rencana Tindakan', 'PIC', 'Target', 'Bukti', 'Status Verifikasi', 'Catatan Auditor'],
            'rows' => $assignment->findings
                ->flatMap(fn (Finding $finding): Collection => $finding->followUps->map(fn (FollowUp $followUp): array => [
                    $finding->nomor_temuan ?: 'Draft #'.$finding->id,
                    $followUp->rencana_tindakan,
                    $followUp->penanggung_jawab,
                    $followUp->target_penyelesaian->format('d/m/Y'),
                    $followUp->evidences->pluck('nama_dokumen')->implode(', ') ?: '-',
                    FollowUp::statusOptions()[$followUp->status] ?? $followUp->status,
                    $followUp->latestVerification?->catatan_verifikasi ?: '-',
                ]))->values()->all(),
        ];
    }

    private function authorizeReport(Request $request, string $report, AuditAssignment $assignment): void
    {
        abort_unless(array_key_exists($report, $this->reportTypes($this->scope($request))), 404);
        abort_unless($this->assignmentQuery($request)->whereKey($assignment->id)->exists(), 403);

        if ($report === 'desk-evaluation' && $request->user()->role === UserRole::Auditee) {
            $hasDraft = $assignment->evaluations()->where('status_pemeriksaan', '!=', 'final')->exists();
            abort_if($hasDraft, 403, 'Lembar desk evaluation hanya dapat diakses setelah final.');
        }
    }

    private function assignmentQuery(Request $request)
    {
        return AuditAssignment::query()
            ->when($request->filled('audit_period_id'), fn ($query) => $query->where('audit_period_id', $request->integer('audit_period_id')))
            ->when($request->filled('unit_id'), fn ($query) => $query->where('unit_id', $request->integer('unit_id')))
            ->where(fn ($query) => $this->scopeAssignmentQuery($request, $query));
    }

    private function scopeAssignmentQuery(Request $request, $query): void
    {
        $user = $request->user();

        if ($user->role === UserRole::Auditor) {
            $query->where(function ($query) use ($user): void {
                $query->where('lead_auditor_id', $user->id)
                    ->orWhereHas('auditors', fn ($query) => $query->where('users.id', $user->id));
            });
        }

        if ($user->role === UserRole::Auditee) {
            $query->where('unit_id', $user->unit_id);
        }
    }

    private function reportTypes(string $scope): array
    {
        $types = [
            'self-assessment' => ['label' => 'Rekap Evaluasi Diri Unit', 'excel' => true],
            'desk-evaluation' => ['label' => 'Lembar Desk Evaluation', 'excel' => false],
            'visit-minutes' => ['label' => 'Berita Acara Visitasi', 'excel' => false],
            'findings' => ['label' => 'Daftar Temuan Audit', 'excel' => true],
            'follow-ups' => ['label' => 'Rekap Tindak Lanjut', 'excel' => true],
            'unit-audit' => ['label' => 'Laporan Hasil Audit Unit', 'excel' => false],
        ];

        if ($scope === 'admin') {
            $types['institution-audit'] = ['label' => 'Rekap Audit Institusi', 'excel' => true, 'institution' => true];
        }

        return $types;
    }

    private function scope(Request $request): string
    {
        return str($request->route()->getName())->before('.')->toString();
    }

    private function pdfLines(array $payload, ?array $printSettings = null): array
    {
        $printSettings ??= reportPrintSettings();
        $lines = [
            $payload['title'],
            $payload['subtitle'],
            '',
        ];

        foreach ($payload['meta'] as $key => $value) {
            $lines[] = "{$key}: {$value}";
        }

        if (($printSettings['show_visual_summary'] ?? true) && ! empty($payload['visuals'])) {
            $lines[] = '';
            $lines[] = 'Ringkasan Visual';
            $lines[] = 'Kesiapan: '.($payload['visuals']['readiness'] ?? 0).'%';

            foreach (($payload['visuals']['radar'] ?? []) as $item) {
                $bar = str_repeat('#', (int) floor(($item['value'] ?? 0) / 5));
                $lines[] = ($item['label'] ?? '-').': '.str_pad($bar, 20, '.').' '.($item['value'] ?? 0).'%';
            }
        }

        foreach ($payload['tables'] as $table) {
            $lines[] = '';
            $lines[] = $table['title'];
            $lines[] = implode(' | ', $table['headers']);

            foreach ($table['rows'] as $row) {
                $lines[] = implode(' | ', array_map(fn ($value): string => str($value)->limit(80)->toString(), $row));
            }
        }

        return $lines;
    }

    private function excelFromPayload(string $filename, array $payload): StreamedResponse
    {
        $table = $payload['tables'][0];

        return ExcelXml::download($filename, str($payload['title'])->limit(24)->toString(), $table['headers'], $table['rows']);
    }

    private function filename(string $report, AuditAssignment $assignment, string $extension): string
    {
        return $report.'-'.$assignment->auditPeriod->id.'-'.$assignment->unit->kode.'.'.$extension;
    }
}

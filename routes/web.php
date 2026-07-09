<?php

use App\Http\Controllers\Admin\AuditAssignmentController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\AuditPeriodController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\InstrumentController;
use App\Http\Controllers\Admin\StandardController;
use App\Http\Controllers\Admin\StandardInstrumentController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UnitUserController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssignmentPortalController;
use App\Http\Controllers\Auditee\ClarificationController as AuditeeClarificationController;
use App\Http\Controllers\Auditee\EvidenceController;
use App\Http\Controllers\Auditee\FollowUpController as AuditeeFollowUpController;
use App\Http\Controllers\Auditee\SelfAssessmentController;
use App\Http\Controllers\Auditee\UnitProfileController;
use App\Http\Controllers\Auditee\VisitController as AuditeeVisitController;
use App\Http\Controllers\Auditor\ClarificationController as AuditorClarificationController;
use App\Http\Controllers\Auditor\DeskEvaluationController;
use App\Http\Controllers\Auditor\FindingController as AuditorFindingController;
use App\Http\Controllers\Auditor\FollowUpVerificationController as AuditorFollowUpVerificationController;
use App\Http\Controllers\Auditor\VisitController as AuditorVisitController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->role->dashboardRoute());
});

Route::get('/brand/logo-jds', fn () => response()->file(resource_path('assets/logo JDS tanpa company.png')))
    ->name('brand.logo');

Route::get('/brand/logo-jds-login', fn () => response()->file(resource_path('assets/logo JDS tanpa company backgroun putih.png')))
    ->name('brand.logo.login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/sync-state', SyncController::class)->name('sync-state');

    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->role->dashboardRoute()))
        ->name('dashboard');

    Route::get('/profile/photo/{user}', [ProfileController::class, 'photo'])->name('profile.photo.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/photo-focus', [ProfileController::class, 'updatePhotoFocus'])->name('profile.photo.focus');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function (): void {
            Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
            Route::get('/periode-audit', [AuditPeriodController::class, 'index'])->name('periods');
            Route::get('/periode-audit/create', [AuditPeriodController::class, 'create'])->name('periods.create');
            Route::post('/periode-audit', [AuditPeriodController::class, 'store'])->name('periods.store');
            Route::post('/periode-audit/bulk-action', [AuditPeriodController::class, 'bulkAction'])->name('periods.bulk-action');
            Route::get('/periode-audit/{period}', [AuditPeriodController::class, 'show'])->name('periods.show');
            Route::get('/periode-audit/{period}/edit', [AuditPeriodController::class, 'edit'])->name('periods.edit');
            Route::put('/periode-audit/{period}', [AuditPeriodController::class, 'update'])->name('periods.update');
            Route::post('/periode-audit/{period}/duplicate', [AuditPeriodController::class, 'duplicate'])->name('periods.duplicate');
            Route::patch('/periode-audit/{period}/activate', [AuditPeriodController::class, 'activate'])->name('periods.activate');
            Route::patch('/periode-audit/{period}/close', [AuditPeriodController::class, 'close'])->name('periods.close');
            Route::patch('/periode-audit/{period}/archive', [AuditPeriodController::class, 'archive'])->name('periods.archive');
            Route::delete('/periode-audit/{period}', [AuditPeriodController::class, 'destroy'])->name('periods.destroy');
            Route::post('/periode-audit/{period}/notify-opening', [AuditPeriodController::class, 'notifyOpening'])->name('periods.notify-opening');

            Route::get('/unit-pengguna', [UnitUserController::class, 'index'])->name('users');
            Route::get('/unit-pengguna/units/export', [UnitController::class, 'export'])->name('units.export');
            Route::get('/unit-pengguna/units/template', [UnitController::class, 'template'])->name('units.template');
            Route::post('/unit-pengguna/units/import', [UnitController::class, 'import'])->name('units.import');
            Route::get('/unit-pengguna/units/create', [UnitController::class, 'create'])->name('units.create');
            Route::post('/unit-pengguna/units', [UnitController::class, 'store'])->name('units.store');
            Route::post('/unit-pengguna/units/bulk-action', [UnitController::class, 'bulkAction'])->name('units.bulk-action');
            Route::get('/unit-pengguna/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
            Route::put('/unit-pengguna/units/{unit}', [UnitController::class, 'update'])->name('units.update');
            Route::patch('/unit-pengguna/units/{unit}/toggle-active', [UnitController::class, 'toggleActive'])->name('units.toggle-active');
            Route::delete('/unit-pengguna/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

            Route::get('/unit-pengguna/users/export', [UserController::class, 'export'])->name('managed-users.export');
            Route::get('/unit-pengguna/users/template', [UserController::class, 'template'])->name('managed-users.template');
            Route::post('/unit-pengguna/users/import', [UserController::class, 'import'])->name('managed-users.import');
            Route::get('/unit-pengguna/users/create', [UserController::class, 'create'])->name('managed-users.create');
            Route::post('/unit-pengguna/users', [UserController::class, 'store'])->name('managed-users.store');
            Route::post('/unit-pengguna/users/bulk-action', [UserController::class, 'bulkAction'])->name('managed-users.bulk-action');
            Route::get('/unit-pengguna/users/{managedUser}/edit', [UserController::class, 'edit'])->name('managed-users.edit');
            Route::put('/unit-pengguna/users/{managedUser}', [UserController::class, 'update'])->name('managed-users.update');
            Route::get('/unit-pengguna/users/{managedUser}/reset-password', [UserController::class, 'editPassword'])->name('managed-users.password.edit');
            Route::patch('/unit-pengguna/users/{managedUser}/reset-password', [UserController::class, 'updatePassword'])->name('managed-users.password.update');
            Route::patch('/unit-pengguna/users/{managedUser}/toggle-active', [UserController::class, 'toggleActive'])->name('managed-users.toggle-active');
            Route::delete('/unit-pengguna/users/{managedUser}', [UserController::class, 'destroy'])->name('managed-users.destroy');

            Route::get('/standar-instrumen', [StandardInstrumentController::class, 'index'])->name('standards');
            Route::get('/standar-instrumen/standards/create', [StandardController::class, 'create'])->name('quality-standards.create');
            Route::post('/standar-instrumen/standards', [StandardController::class, 'store'])->name('quality-standards.store');
            Route::post('/standar-instrumen/standards/bulk-action', [StandardController::class, 'bulkAction'])->name('quality-standards.bulk-action');
            Route::get('/standar-instrumen/standards/{standard}/edit', [StandardController::class, 'edit'])->name('quality-standards.edit');
            Route::put('/standar-instrumen/standards/{standard}', [StandardController::class, 'update'])->name('quality-standards.update');
            Route::patch('/standar-instrumen/standards/{standard}/toggle-active', [StandardController::class, 'toggleActive'])->name('quality-standards.toggle-active');
            Route::patch('/standar-instrumen/standards/{standard}/move/{direction}', [StandardController::class, 'move'])->whereIn('direction', ['up', 'down'])->name('quality-standards.move');
            Route::delete('/standar-instrumen/standards/{standard}', [StandardController::class, 'destroy'])->name('quality-standards.destroy');

            Route::get('/standar-instrumen/instruments/export', [InstrumentController::class, 'export'])->name('instruments.export');
            Route::get('/standar-instrumen/instruments/template', [InstrumentController::class, 'template'])->name('instruments.template');
            Route::get('/standar-instrumen/instruments/template/{standard}', [InstrumentController::class, 'templateForStandard'])->name('instruments.template.standard');
            Route::post('/standar-instrumen/instruments/import', [InstrumentController::class, 'import'])->name('instruments.import');
            Route::get('/standar-instrumen/instruments/create', [InstrumentController::class, 'create'])->name('instruments.create');
            Route::post('/standar-instrumen/instruments', [InstrumentController::class, 'store'])->name('instruments.store');
            Route::get('/standar-instrumen/instruments/{instrument}/edit', [InstrumentController::class, 'edit'])->name('instruments.edit');
            Route::put('/standar-instrumen/instruments/{instrument}', [InstrumentController::class, 'update'])->name('instruments.update');
            Route::post('/standar-instrumen/instruments/{instrument}/duplicate', [InstrumentController::class, 'duplicate'])->name('instruments.duplicate');
            Route::patch('/standar-instrumen/instruments/{instrument}/toggle-active', [InstrumentController::class, 'toggleActive'])->name('instruments.toggle-active');
            Route::post('/standar-instrumen/instruments/bulk-action', [InstrumentController::class, 'bulkAction'])->name('instruments.bulk-action');
            Route::delete('/standar-instrumen/instruments/bulk', [InstrumentController::class, 'bulkDestroy'])->name('instruments.bulk-destroy');
            Route::delete('/standar-instrumen/instruments/{instrument}', [InstrumentController::class, 'destroy'])->name('instruments.destroy');
            Route::get('/penugasan-audit', [AuditAssignmentController::class, 'index'])->name('assignments');
            Route::get('/penugasan-audit/create', [AuditAssignmentController::class, 'create'])->name('assignments.create');
            Route::post('/penugasan-audit', [AuditAssignmentController::class, 'store'])->name('assignments.store');
            Route::post('/penugasan-audit/bulk-action', [AuditAssignmentController::class, 'bulkAction'])->name('assignments.bulk-action');
            Route::get('/penugasan-audit/{assignment}', [AuditAssignmentController::class, 'show'])->name('assignments.show');
            Route::get('/penugasan-audit/{assignment}/edit', [AuditAssignmentController::class, 'edit'])->name('assignments.edit');
            Route::put('/penugasan-audit/{assignment}', [AuditAssignmentController::class, 'update'])->name('assignments.update');
            Route::patch('/penugasan-audit/{assignment}/cancel', [AuditAssignmentController::class, 'cancel'])->name('assignments.cancel');
            Route::delete('/penugasan-audit/{assignment}', [AuditAssignmentController::class, 'destroy'])->name('assignments.destroy');
            Route::post('/penugasan-audit/{assignment}/notify', [AuditAssignmentController::class, 'notify'])->name('assignments.notify');
            Route::get('/penugasan-audit/{assignment}/surat-tugas', [AuditAssignmentController::class, 'printLetter'])->name('assignments.print-letter');
            Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
            Route::post('/monitoring/{assignment}/reminder', [MonitoringController::class, 'sendReminder'])->name('monitoring.reminder');
            Route::post('/monitoring/reminders/self-evaluation', [MonitoringController::class, 'sendSelfEvaluationReminders'])->name('monitoring.self-evaluation-reminders');
            Route::get('/monitoring/export/progress', [MonitoringController::class, 'exportProgress'])->name('monitoring.export.progress');
            Route::get('/monitoring/export/late-findings', [MonitoringController::class, 'exportLateFindings'])->name('monitoring.export.late-findings');
            Route::get('/laporan', [ReportController::class, 'index'])->name('reports');
            Route::get('/laporan/institusi/preview', [ReportController::class, 'institutionPreview'])->name('reports.institution.preview');
            Route::get('/laporan/institusi/download', [ReportController::class, 'institutionDownload'])->name('reports.institution.download');
            Route::get('/laporan/institusi/excel', [ReportController::class, 'institutionExcel'])->name('reports.institution.excel');
            Route::get('/laporan/{report}/{assignment}/preview', [ReportController::class, 'preview'])->name('reports.preview');
            Route::get('/laporan/{report}/{assignment}/download', [ReportController::class, 'download'])->name('reports.download');
            Route::get('/laporan/{report}/{assignment}/excel', [ReportController::class, 'excel'])->name('reports.excel');
            Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings');
            Route::patch('/pengaturan/identity', [SettingController::class, 'updateIdentity'])->name('settings.identity');
            Route::patch('/pengaturan/upload', [SettingController::class, 'updateUpload'])->name('settings.upload');
            Route::patch('/pengaturan/report-format', [SettingController::class, 'updateReportFormat'])->name('settings.report-format');
            Route::get('/pengaturan/report-format/template/pdf', [SettingController::class, 'letterheadTemplatePdf'])->name('settings.report-format.template.pdf');
            Route::get('/pengaturan/report-format/template/docx', [SettingController::class, 'letterheadTemplateDocx'])->name('settings.report-format.template.docx');
            Route::post('/pengaturan/advanced/reset-public', [SettingController::class, 'resetPublic'])->name('settings.advanced.reset-public');
            Route::post('/pengaturan/categories', [SettingController::class, 'storeCategory'])->name('settings.categories.store');
            Route::put('/pengaturan/categories/{category}', [SettingController::class, 'updateCategory'])->name('settings.categories.update');
            Route::patch('/pengaturan/categories/{category}/toggle', [SettingController::class, 'toggleCategory'])->name('settings.categories.toggle');
            Route::patch('/pengaturan/templates', [SettingController::class, 'updateTemplates'])->name('settings.templates');
        });

    Route::prefix('auditor')
        ->name('auditor.')
        ->middleware('role:auditor')
        ->group(function (): void {
            Route::get('/dashboard', [DashboardController::class, 'auditor'])->name('dashboard');
            Route::get('/panduan', [GuideController::class, 'auditor'])->name('guide');
            Route::get('/tugas-audit', [AssignmentPortalController::class, 'auditorTasks'])->name('tasks');
            Route::get('/desk-evaluation', [DeskEvaluationController::class, 'index'])->name('desk-evaluation');
            Route::get('/desk-evaluation/{assignment}', [DeskEvaluationController::class, 'show'])->name('desk-evaluation.show');
            Route::patch('/desk-evaluation/{assignment}/evaluations/{evaluation}', [DeskEvaluationController::class, 'update'])->name('desk-evaluation.update');
            Route::post('/desk-evaluation/{assignment}/evaluations/{evaluation}/clarification', [DeskEvaluationController::class, 'requestClarification'])->name('desk-evaluation.clarification');
            Route::post('/desk-evaluation/{assignment}/finalize', [DeskEvaluationController::class, 'finalize'])->name('desk-evaluation.finalize');
            Route::get('/desk-evaluation/evidences/{evidence}/preview', [DeskEvaluationController::class, 'previewEvidence'])->name('desk-evaluation.evidences.preview');
            Route::get('/desk-evaluation/evidences/{evidence}/download', [DeskEvaluationController::class, 'downloadEvidence'])->name('desk-evaluation.evidences.download');
            Route::get('/klarifikasi', [AuditorClarificationController::class, 'index'])->name('clarifications');
            Route::get('/klarifikasi/evidences/{evidence}/download', [AuditorClarificationController::class, 'downloadEvidence'])->name('clarifications.evidences.download');
            Route::get('/klarifikasi/{clarification}', [AuditorClarificationController::class, 'show'])->name('clarifications.show');
            Route::post('/klarifikasi/{clarification}/messages', [AuditorClarificationController::class, 'storeMessage'])->name('clarifications.messages.store');
            Route::post('/klarifikasi/{clarification}/evidences', [AuditorClarificationController::class, 'storeEvidence'])->name('clarifications.evidences.store');
            Route::patch('/klarifikasi/{clarification}/finish', [AuditorClarificationController::class, 'finish'])->name('clarifications.finish');
            Route::patch('/klarifikasi/{clarification}/reopen', [AuditorClarificationController::class, 'reopen'])->name('clarifications.reopen');
            Route::get('/visitasi', [AuditorVisitController::class, 'index'])->name('visitations');
            Route::get('/visitasi/attachments/{attachment}/download', [AuditorVisitController::class, 'downloadAttachment'])->name('visitations.attachments.download');
            Route::get('/visitasi/{assignment}', [AuditorVisitController::class, 'show'])->name('visitations.show');
            Route::post('/visitasi/{assignment}', [AuditorVisitController::class, 'save'])->name('visitations.save');
            Route::post('/visitasi/{assignment}/participants', [AuditorVisitController::class, 'addParticipant'])->name('visitations.participants.store');
            Route::post('/visitasi/{assignment}/attachments', [AuditorVisitController::class, 'storeAttachment'])->name('visitations.attachments.store');
            Route::patch('/visitasi/{assignment}/finish', [AuditorVisitController::class, 'finish'])->name('visitations.finish');
            Route::post('/visitasi/{assignment}/send-minutes', [AuditorVisitController::class, 'sendMinutes'])->name('visitations.send-minutes');
            Route::get('/visitasi/{assignment}/berita-acara', [AuditorVisitController::class, 'minutes'])->name('visitations.minutes');
            Route::get('/temuan', [AuditorFindingController::class, 'index'])->name('findings');
            Route::get('/temuan/create', [AuditorFindingController::class, 'create'])->name('findings.create');
            Route::post('/temuan', [AuditorFindingController::class, 'store'])->name('findings.store');
            Route::get('/temuan/cetak', [AuditorFindingController::class, 'print'])->name('findings.print');
            Route::get('/temuan/{finding}', [AuditorFindingController::class, 'show'])->name('findings.show');
            Route::get('/temuan/{finding}/edit', [AuditorFindingController::class, 'edit'])->name('findings.edit');
            Route::put('/temuan/{finding}', [AuditorFindingController::class, 'update'])->name('findings.update');
            Route::patch('/temuan/{finding}/finalize', [AuditorFindingController::class, 'finalize'])->name('findings.finalize');
            Route::patch('/temuan/{finding}/cancel', [AuditorFindingController::class, 'cancel'])->name('findings.cancel');
            Route::get('/verifikasi-tindak-lanjut', [AuditorFollowUpVerificationController::class, 'index'])->name('follow-up-verifications');
            Route::get('/verifikasi-tindak-lanjut/evidences/{evidence}/download', [AuditorFollowUpVerificationController::class, 'downloadEvidence'])->name('follow-up-verifications.evidences.download');
            Route::get('/verifikasi-tindak-lanjut/{followUp}', [AuditorFollowUpVerificationController::class, 'show'])->name('follow-up-verifications.show');
            Route::post('/verifikasi-tindak-lanjut/{followUp}/verify', [AuditorFollowUpVerificationController::class, 'verify'])->name('follow-up-verifications.verify');
            Route::get('/laporan-saya', [ReportController::class, 'index'])->name('reports');
            Route::get('/laporan-saya/{report}/{assignment}/preview', [ReportController::class, 'preview'])->name('reports.preview');
            Route::get('/laporan-saya/{report}/{assignment}/download', [ReportController::class, 'download'])->name('reports.download');
            Route::get('/laporan-saya/{report}/{assignment}/excel', [ReportController::class, 'excel'])->name('reports.excel');
        });

    Route::prefix('auditee')
        ->name('auditee.')
        ->middleware('role:auditee')
        ->group(function (): void {
            Route::get('/dashboard', [DashboardController::class, 'auditee'])->name('dashboard');
            Route::get('/panduan', [GuideController::class, 'auditee'])->name('guide');
            Route::get('/profil-unit', [UnitProfileController::class, 'show'])->name('unit-profile');
            Route::get('/evaluasi-diri', [SelfAssessmentController::class, 'index'])->name('self-evaluations');
            Route::get('/evaluasi-diri/{assessment}/edit', [SelfAssessmentController::class, 'edit'])->name('self-assessments.edit');
            Route::patch('/evaluasi-diri/{assessment}/draft', [SelfAssessmentController::class, 'saveDraft'])->name('self-assessments.draft');
            Route::patch('/evaluasi-diri/{assessment}/submit', [SelfAssessmentController::class, 'submit'])->name('self-assessments.submit');
            Route::patch('/evaluasi-diri/{assessment}/withdraw', [SelfAssessmentController::class, 'withdraw'])->name('self-assessments.withdraw');
            Route::post('/evaluasi-diri/{assignment}/finalize', [SelfAssessmentController::class, 'finalize'])->name('self-assessments.finalize');
            Route::post('/evaluasi-diri/{assessment}/evidences', [SelfAssessmentController::class, 'storeEvidence'])->name('self-assessments.evidences.store');
            Route::delete('/evaluasi-diri/evidences/{evidence}', [SelfAssessmentController::class, 'deleteEvidence'])->name('self-assessments.evidences.destroy');

            Route::get('/bukti-dokumen', [EvidenceController::class, 'index'])->name('documents');
            Route::get('/bukti-dokumen/create', [EvidenceController::class, 'create'])->name('documents.create');
            Route::post('/bukti-dokumen', [EvidenceController::class, 'store'])->name('documents.store');
            Route::get('/bukti-dokumen/{evidence}/preview', [EvidenceController::class, 'preview'])->name('documents.preview');
            Route::get('/bukti-dokumen/{evidence}/download', [EvidenceController::class, 'download'])->name('documents.download');
            Route::delete('/bukti-dokumen/{evidence}', [EvidenceController::class, 'destroy'])->name('documents.destroy');
            Route::get('/klarifikasi-auditor', [AuditeeClarificationController::class, 'index'])->name('clarifications');
            Route::get('/klarifikasi-auditor/evidences/{evidence}/download', [AuditeeClarificationController::class, 'downloadEvidence'])->name('clarifications.evidences.download');
            Route::get('/klarifikasi-auditor/{clarification}', [AuditeeClarificationController::class, 'show'])->name('clarifications.show');
            Route::post('/klarifikasi-auditor/{clarification}/messages', [AuditeeClarificationController::class, 'storeMessage'])->name('clarifications.messages.store');
            Route::post('/klarifikasi-auditor/{clarification}/evidences', [AuditeeClarificationController::class, 'storeEvidence'])->name('clarifications.evidences.store');
            Route::patch('/klarifikasi-auditor/{clarification}/answered', [AuditeeClarificationController::class, 'markAnswered'])->name('clarifications.answered');
            Route::get('/jadwal-visitasi', [AuditeeVisitController::class, 'index'])->name('visit-schedules');
            Route::get('/jadwal-visitasi/attachments/{attachment}/download', [AuditeeVisitController::class, 'downloadAttachment'])->name('visit-schedules.attachments.download');
            Route::get('/jadwal-visitasi/{visit}', [AuditeeVisitController::class, 'show'])->name('visit-schedules.show');
            Route::patch('/jadwal-visitasi/{visit}/confirm', [AuditeeVisitController::class, 'confirm'])->name('visit-schedules.confirm');
            Route::post('/jadwal-visitasi/{visit}/attachments', [AuditeeVisitController::class, 'storeAttachment'])->name('visit-schedules.attachments.store');
            Route::get('/jadwal-visitasi/{visit}/berita-acara', [AuditeeVisitController::class, 'minutes'])->name('visit-schedules.minutes');
            Route::get('/temuan-tindak-lanjut', [AuditeeFollowUpController::class, 'index'])->name('findings-followups');
            Route::get('/temuan-tindak-lanjut/evidences/{evidence}/download', [AuditeeFollowUpController::class, 'downloadEvidence'])->name('findings-followups.evidences.download');
            Route::get('/temuan-tindak-lanjut/{finding}', [AuditeeFollowUpController::class, 'show'])->name('findings-followups.show');
            Route::post('/temuan-tindak-lanjut/{finding}/follow-up', [AuditeeFollowUpController::class, 'save'])->name('findings-followups.save');
            Route::post('/temuan-tindak-lanjut/{finding}/follow-up/evidences', [AuditeeFollowUpController::class, 'storeEvidence'])->name('findings-followups.evidences.store');
            Route::post('/temuan-tindak-lanjut/{finding}/follow-up/submit', [AuditeeFollowUpController::class, 'submit'])->name('findings-followups.submit');
            Route::get('/laporan-unit', [ReportController::class, 'index'])->name('reports');
            Route::get('/laporan-unit/{report}/{assignment}/preview', [ReportController::class, 'preview'])->name('reports.preview');
            Route::get('/laporan-unit/{report}/{assignment}/download', [ReportController::class, 'download'])->name('reports.download');
            Route::get('/laporan-unit/{report}/{assignment}/excel', [ReportController::class, 'excel'])->name('reports.excel');
        });
});

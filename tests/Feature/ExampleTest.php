<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\SiamiNotificationMail;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Clarification;
use App\Models\ClarificationEvidence;
use App\Models\Evaluation;
use App\Models\Evidence;
use App\Models\Finding;
use App\Models\FollowUp;
use App\Models\Instrument;
use App\Models\Notification as InAppNotification;
use App\Models\Setting;
use App\Models\SelfAssessment;
use App\Models\Standard;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_role_area(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect('/login');
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        User::factory()->create([
            'email' => 'admin@siami.test',
            'role' => UserRole::Admin,
        ]);

        $this->post('/login', [
            'email' => 'admin@siami.test',
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');
    }

    public function test_authenticated_user_cannot_access_another_role_area(): void
    {
        $auditor = User::factory()->create([
            'role' => UserRole::Auditor,
        ]);

        $this->actingAs($auditor)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_dashboard_displays_operational_cards(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Total Unit')
            ->assertSee('Progres Evaluasi Diri')
            ->assertSee('Aktivitas Terbaru');
    }

    public function test_auditor_dashboard_displays_task_indicators(): void
    {
        [$auditor, $assignment] = $this->deskEvaluationFixture();

        $this->actingAs($auditor)
            ->get('/auditor/dashboard')
            ->assertOk()
            ->assertSee('Tugas Aktif')
            ->assertSee('Instrumen Belum Diperiksa')
            ->assertSee($assignment->unit->nama);
    }

    public function test_auditee_dashboard_displays_self_evaluation_indicators(): void
    {
        [$auditee] = $this->auditeeAssignment();
        $this->seedInstrument('S1-01');

        $this->actingAs($auditee)
            ->get('/auditee/dashboard')
            ->assertOk()
            ->assertSee('Total Instrumen')
            ->assertSee('Kesiapan Evaluasi Diri')
            ->assertSee('Jadwal Visitasi');
    }

    public function test_auditee_can_open_unit_profile(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();

        $this->actingAs($auditee)
            ->get('/auditee/profil-unit')
            ->assertOk()
            ->assertSee('Profil Unit')
            ->assertSee($assignment->unit->nama)
            ->assertSee($assignment->leadAuditor->name)
            ->assertSee('Status Evaluasi Diri');
    }

    public function test_auditor_can_open_role_guide(): void
    {
        $auditor = User::factory()->create([
            'role' => UserRole::Auditor,
        ]);

        $this->actingAs($auditor)
            ->get('/auditor/panduan')
            ->assertOk()
            ->assertSee('Panduan Auditor')
            ->assertSee('Desk Evaluation')
            ->assertSee('Verifikasi Perbaikan');
    }

    public function test_auditor_can_open_task_workspace(): void
    {
        [$auditor, $assignment] = $this->deskEvaluationFixture();

        $this->actingAs($auditor)
            ->get('/auditor/tugas-audit')
            ->assertOk()
            ->assertSee('Tugas Audit')
            ->assertSee($assignment->unit->nama)
            ->assertSee('Workspace Auditor')
            ->assertSee('Periksa')
            ->assertSee('Temuan Aktif');
    }

    public function test_auditee_can_open_role_guide(): void
    {
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
        ]);

        $this->actingAs($auditee)
            ->get('/auditee/panduan')
            ->assertOk()
            ->assertSee('Panduan Auditee')
            ->assertSee('Evaluasi Diri')
            ->assertSee('Tindak Lanjut Temuan');
    }

    public function test_admin_monitoring_can_send_manual_reminder(): void
    {
        [$admin, , $unit, $auditor] = $this->assignmentActors();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $unit->id,
        ]);
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => AuditPeriod::query()->where('status', 'aktif')->first()->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->get('/admin/monitoring')
            ->assertOk()
            ->assertSee('Progres Unit')
            ->assertSee($unit->nama);

        $this->actingAs($admin)
            ->post("/admin/monitoring/{$assignment->id}/reminder", [
                'process' => 'evaluasi diri',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'pengingat_manual',
            'is_read' => false,
        ]);
    }

    public function test_user_can_open_and_mark_in_app_notification_as_read(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Auditee,
        ]);
        $notification = InAppNotification::sendNotification(
            $user->id,
            'pengingat_manual',
            'Pengingat dari Admin',
            'Pengingat dari Admin: mohon segera melengkapi evaluasi diri.',
            route('auditee.dashboard', absolute: false),
            'audit_assignment',
            10,
        );

        $this->actingAs($user)
            ->get('/notifications')
            ->assertOk()
            ->assertSee('Pengingat dari Admin');

        $this->actingAs($user)
            ->get("/notifications/{$notification->id}")
            ->assertRedirect('/auditee/dashboard');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_in_app_notification_sends_email_to_auditee(): void
    {
        Mail::fake();
        Setting::putValue('email_notifications_enabled', '1');
        $user = User::factory()->create([
            'role' => UserRole::Auditee,
            'email' => 'anggreaputrabagus@gmail.com',
            'is_active' => true,
        ]);

        InAppNotification::sendNotification(
            $user->id,
            'visitasi_dijadwalkan',
            'Jadwal Visitasi',
            'Jadwal visitasi telah ditetapkan auditor.',
            route('auditee.dashboard', absolute: false),
            'visit',
            1,
        );

        Mail::assertSent(SiamiNotificationMail::class, function (SiamiNotificationMail $mail) use ($user): bool {
            return $mail->hasTo($user->email)
                && $mail->title === 'Jadwal Visitasi';
        });
    }

    public function test_user_can_upload_view_and_delete_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'role' => UserRole::Auditor,
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '08123456789',
                'profile_photo' => UploadedFile::fake()->image('avatar.jpg', 256, 256),
                'profile_photo_focus_x' => 35,
                'profile_photo_focus_y' => 42,
            ])
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        $this->assertSame(35, $user->profile_photo_focus_x);
        $this->assertSame(42, $user->profile_photo_focus_y);
        Storage::disk('public')->assertExists($user->profile_photo_path);

        $this->actingAs($user)
            ->get("/profile/photo/{$user->id}")
            ->assertOk();

        $this->actingAs($user)
            ->patch('/profile/photo-focus', [
                'profile_photo_focus_x' => 72,
                'profile_photo_focus_y' => 24,
            ])
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertSame(72, $user->profile_photo_focus_x);
        $this->assertSame(24, $user->profile_photo_focus_y);

        $path = $user->profile_photo_path;

        $this->actingAs($user)
            ->delete('/profile/photo')
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertNull($user->profile_photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_open_reports_and_download_outputs(): void
    {
        [$auditor, $assignment] = $this->deskEvaluationFixture();
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/admin/laporan')
            ->assertOk()
            ->assertSee('Rekap Evaluasi Diri Unit')
            ->assertSee($assignment->unit->nama);

        $this->actingAs($admin)
            ->get("/admin/laporan/self-assessment/{$assignment->id}/preview")
            ->assertOk()
            ->assertSee('Rekap Evaluasi Diri Unit')
            ->assertSee('Apakah dokumen VMTS tersedia?');

        $this->actingAs($admin)
            ->get("/admin/laporan/self-assessment/{$assignment->id}/download")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)
            ->get("/admin/laporan/self-assessment/{$assignment->id}/excel")
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');

        $this->actingAs($admin)
            ->get('/admin/laporan/institusi/preview')
            ->assertOk()
            ->assertSee('Rekap Audit Institusi');
    }

    public function test_auditor_reports_are_scoped_to_assignments(): void
    {
        [$auditor, $assignment] = $this->deskEvaluationFixture();
        $otherAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);

        $this->actingAs($auditor)
            ->get('/auditor/laporan-saya')
            ->assertOk()
            ->assertSee($assignment->unit->nama);

        $this->actingAs($otherAuditor)
            ->get("/auditor/laporan-saya/self-assessment/{$assignment->id}/preview")
            ->assertForbidden();
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/admin/pengaturan')
            ->assertOk()
            ->assertSee('Identitas Institusi');

        $this->actingAs($admin)
            ->patch('/admin/pengaturan/identity', [
                'nama_institusi' => 'Universitas Pengujian',
                'nama_lpm' => 'Unit Mutu',
                'email_lpm' => 'mutu@siami.test',
            ])
            ->assertSessionHas('status');

        $this->actingAs($admin)
            ->patch('/admin/pengaturan/upload', [
                'max_file_size_mb' => 12,
                'allowed_file_types' => ['pdf', 'jpg'],
            ])
            ->assertSessionHas('status');

        $this->assertSame('Universitas Pengujian', Setting::getValue('nama_institusi'));
        $this->assertSame('12', Setting::getValue('max_file_size_mb'));
        $this->assertSame('pdf,jpg', Setting::getValue('allowed_file_types'));
    }

    public function test_admin_can_open_unit_and_user_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/admin/unit-pengguna')
            ->assertOk()
            ->assertSee('Data Unit')
            ->assertSee('Data Pengguna');
    }

    public function test_admin_can_create_unit(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->post('/admin/unit-pengguna/units', [
                'kode' => 'TI',
                'nama' => 'Program Studi Teknik Informatika',
                'jenis_unit' => 'prodi',
                'nama_pimpinan' => 'Ketua Prodi',
                'email' => 'ti@siami.test',
                'phone' => '080000000004',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/unit-pengguna?tab=units');

        $this->assertDatabaseHas('units', [
            'kode' => 'TI',
            'nama' => 'Program Studi Teknik Informatika',
            'is_active' => true,
        ]);
    }

    public function test_auditee_user_requires_unit(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->from('/admin/unit-pengguna/users/create')
            ->post('/admin/unit-pengguna/users', [
                'name' => 'Kaprodi Baru',
                'email' => 'kaprodi@siami.test',
                'role' => UserRole::Auditee->value,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/unit-pengguna/users/create')
            ->assertSessionHasErrors('unit_id');
    }

    public function test_admin_can_create_auditee_user_with_unit(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $unit = Unit::query()->create([
            'kode' => 'AK',
            'nama' => 'Program Studi Akuntansi',
            'jenis_unit' => 'prodi',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/unit-pengguna/users', [
                'name' => 'Kaprodi Akuntansi',
                'nip_nidn' => '0000001001',
                'email' => 'kaprodi-ak@siami.test',
                'phone' => '080000000005',
                'role' => UserRole::Auditee->value,
                'unit_id' => $unit->id,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/unit-pengguna?tab=users');

        $this->assertDatabaseHas('users', [
            'email' => 'kaprodi-ak@siami.test',
            'role' => UserRole::Auditee->value,
            'unit_id' => $unit->id,
        ]);
    }

    public function test_admin_can_open_standard_and_instrument_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/admin/standar-instrumen')
            ->assertOk()
            ->assertSee('Standar')
            ->assertSee('Instrumen');
    }

    public function test_admin_can_create_standard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->post('/admin/standar-instrumen/standards', [
                'kode' => 'S3',
                'nama' => 'Mahasiswa',
                'deskripsi' => 'Standar mahasiswa',
                'target' => 'Layanan mahasiswa berjalan efektif.',
                'urutan' => 3,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/standar-instrumen?tab=standards');

        $this->assertDatabaseHas('standards', [
            'kode' => 'S3',
            'nama' => 'Mahasiswa',
            'is_active' => true,
        ]);
    }

    public function test_choice_instrument_requires_two_options(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $standard = Standard::query()->create([
            'kode' => 'S1',
            'nama' => 'Visi Misi',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from('/admin/standar-instrumen/instruments/create')
            ->post('/admin/standar-instrumen/instruments', [
                'standard_id' => $standard->id,
                'kode' => 'S1-01',
                'pertanyaan' => 'Pilih kondisi VMTS.',
                'jenis_jawaban' => 'pilihan',
                'target_kriteria' => 'Tersedia pilihan valid.',
                'bukti_diperlukan' => 'Dokumen pendukung.',
                'opsi_jawaban' => 'Satu opsi',
                'urutan' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/standar-instrumen/instruments/create')
            ->assertSessionHasErrors('opsi_jawaban');
    }

    public function test_admin_can_create_and_duplicate_instrument(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $standard = Standard::query()->create([
            'kode' => 'S1',
            'nama' => 'Visi Misi',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/standar-instrumen/instruments', [
                'standard_id' => $standard->id,
                'kode' => 'S1-01',
                'pertanyaan' => 'Apakah dokumen VMTS tersedia?',
                'jenis_jawaban' => 'narasi',
                'target_kriteria' => 'Dokumen tersedia.',
                'bukti_diperlukan' => 'Dokumen VMTS.',
                'urutan' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/standar-instrumen?tab=instruments');

        $instrument = Instrument::query()->firstOrFail();

        $this->actingAs($admin)
            ->post("/admin/standar-instrumen/instruments/{$instrument->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('instruments', [
            'standard_id' => $standard->id,
            'kode' => 'S1-01-COPY',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_open_audit_period_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/admin/periode-audit')
            ->assertOk()
            ->assertSee('Periode Audit')
            ->assertSee('Tambah Periode');
    }

    public function test_audit_period_dates_must_be_ordered(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->from('/admin/periode-audit/create')
            ->post('/admin/periode-audit', [
                'nama' => 'AMI Ganjil',
                'tahun_akademik' => '2026/2027',
                'jenis_audit' => 'akademik',
                'tanggal_mulai' => '2026-08-10',
                'batas_evaluasi_diri' => '2026-08-01',
                'batas_desk_evaluation' => '2026-08-20',
                'batas_tindak_lanjut' => '2026-09-10',
            ])
            ->assertRedirect('/admin/periode-audit/create')
            ->assertSessionHasErrors('batas_evaluasi_diri');
    }

    public function test_only_one_audit_period_can_be_active(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        AuditPeriod::query()->create($this->periodPayload($admin, [
            'nama' => 'Periode Aktif',
            'status' => 'aktif',
        ]));
        $draft = AuditPeriod::query()->create($this->periodPayload($admin, [
            'nama' => 'Periode Draft',
            'status' => 'draft',
        ]));

        $this->actingAs($admin)
            ->patch("/admin/periode-audit/{$draft->id}/activate")
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('audit_periods', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);
    }

    public function test_audit_period_status_moves_forward(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $period = AuditPeriod::query()->create($this->periodPayload($admin, [
            'status' => 'draft',
        ]));

        $this->actingAs($admin)
            ->patch("/admin/periode-audit/{$period->id}/activate")
            ->assertSessionHas('status');

        $period->refresh();
        $this->assertSame('aktif', $period->status);

        $this->actingAs($admin)
            ->patch("/admin/periode-audit/{$period->id}/close", ['force_close' => '1'])
            ->assertSessionHas('status');

        $period->refresh();
        $this->assertSame('ditutup', $period->status);

        $this->actingAs($admin)
            ->patch("/admin/periode-audit/{$period->id}/archive")
            ->assertSessionHas('status');

        $period->refresh();
        $this->assertSame('diarsipkan', $period->status);
    }

    public function test_archived_period_cannot_be_edited(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $period = AuditPeriod::query()->create($this->periodPayload($admin, [
            'status' => 'diarsipkan',
        ]));

        $this->actingAs($admin)
            ->put("/admin/periode-audit/{$period->id}", [
                'nama' => 'Nama Baru',
                'tahun_akademik' => '2026/2027',
                'jenis_audit' => 'akademik',
                'tanggal_mulai' => '2026-08-01',
                'batas_evaluasi_diri' => '2026-08-10',
                'batas_desk_evaluation' => '2026-08-20',
                'batas_tindak_lanjut' => '2026-09-10',
            ])
            ->assertRedirect("/admin/periode-audit/{$period->id}")
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('audit_periods', [
            'id' => $period->id,
            'nama' => $period->nama,
        ]);
    }

    public function test_admin_can_send_opening_notifications(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        User::factory()->create([
            'role' => UserRole::Auditor,
        ]);
        User::factory()->create([
            'role' => UserRole::Auditee,
        ]);
        $period = AuditPeriod::query()->create($this->periodPayload($admin, [
            'status' => 'aktif',
        ]));

        $this->actingAs($admin)
            ->post("/admin/periode-audit/{$period->id}/notify-opening")
            ->assertSessionHas('status');

        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_admin_can_open_audit_assignment_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/admin/penugasan-audit')
            ->assertOk()
            ->assertSee('Penugasan Audit')
            ->assertSee('Tambah Penugasan');
    }

    public function test_admin_can_create_assignment_and_notifications_are_sent(): void
    {
        [$admin, $period, $unit, $leadAuditor, $memberAuditor] = $this->assignmentActors();
        User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $unit->id,
        ]);

        $this->actingAs($admin)
            ->post('/admin/penugasan-audit', [
                'audit_period_id' => $period->id,
                'unit_id' => $unit->id,
                'lead_auditor_id' => $leadAuditor->id,
                'member_auditor_ids' => [$memberAuditor->id],
                'tanggal_desk_evaluation' => '2026-08-15',
                'jadwal_visitasi' => '2026-08-25',
                'catatan_penugasan' => 'Audit dokumen evaluasi diri.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_assignments', [
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $leadAuditor->id,
            'status' => 'aktif',
        ]);
        $this->assertDatabaseCount('notifications', 3);
    }

    public function test_unit_can_only_have_one_active_assignment_per_period(): void
    {
        [$admin, $period, $unit, $leadAuditor] = $this->assignmentActors();
        AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $leadAuditor->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->from('/admin/penugasan-audit/create')
            ->post('/admin/penugasan-audit', [
                'audit_period_id' => $period->id,
                'unit_id' => $unit->id,
                'lead_auditor_id' => $leadAuditor->id,
            ])
            ->assertRedirect('/admin/penugasan-audit/create')
            ->assertSessionHasErrors('unit_id');
    }

    public function test_admin_can_cancel_assignment_without_deleting_it(): void
    {
        [$admin, $period, $unit, $leadAuditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $leadAuditor->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->patch("/admin/penugasan-audit/{$assignment->id}/cancel")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('audit_assignments', [
            'id' => $assignment->id,
            'status' => 'dibatalkan',
        ]);
    }

    public function test_assignment_letter_is_generated_as_pdf(): void
    {
        [$admin, $period, $unit, $leadAuditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $leadAuditor->id,
            'status' => 'aktif',
        ]);
        $assignment->auditors()->sync([
            $leadAuditor->id => ['peran_dalam_tim' => 'lead'],
        ]);

        $this->actingAs($admin)
            ->get("/admin/penugasan-audit/{$assignment->id}/surat-tugas")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_auditee_can_open_self_assessment_list_for_own_unit(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $this->seedInstrument('S1-01');

        $this->actingAs($auditee)
            ->get('/auditee/evaluasi-diri')
            ->assertOk()
            ->assertSee('Evaluasi Diri')
            ->assertSee($assignment->unit->nama);

        $this->assertDatabaseCount('self_assessments', 1);
    }

    public function test_auditee_cannot_open_other_unit_assessment(): void
    {
        [$auditee] = $this->auditeeAssignment();
        $other = $this->auditeeAssignment('AK', 'Program Studi Akuntansi')[1];
        $instrument = $this->seedInstrument('S1-01');
        $assessment = SelfAssessment::query()->create([
            'assignment_id' => $other->id,
            'instrument_id' => $instrument->id,
            'target' => $instrument->target_kriteria,
        ]);

        $this->actingAs($auditee)
            ->get("/auditee/evaluasi-diri/{$assessment->id}/edit")
            ->assertForbidden();
    }

    public function test_auditee_can_save_submit_and_withdraw_assessment(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $instrument = $this->seedInstrument('S1-01');
        $assessment = SelfAssessment::query()->create([
            'assignment_id' => $assignment->id,
            'instrument_id' => $instrument->id,
            'target' => $instrument->target_kriteria,
        ]);

        $this->actingAs($auditee)
            ->patch("/auditee/evaluasi-diri/{$assessment->id}/draft", [
                'jawaban_naratif' => 'Dokumen VMTS tersedia.',
                'realisasi' => 'Tersedia',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('self_assessments', [
            'id' => $assessment->id,
            'status' => 'draft',
        ]);

        $this->actingAs($auditee)
            ->patch("/auditee/evaluasi-diri/{$assessment->id}/submit", [
                'jawaban_naratif' => 'Dokumen VMTS tersedia.',
                'realisasi' => 'Tersedia',
            ])
            ->assertRedirect('/auditee/evaluasi-diri');

        $this->assertDatabaseHas('self_assessments', [
            'id' => $assessment->id,
            'status' => 'dikirim',
        ]);

        $this->actingAs($auditee)
            ->patch("/auditee/evaluasi-diri/{$assessment->id}/withdraw")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('self_assessments', [
            'id' => $assessment->id,
            'status' => 'draft',
        ]);
    }

    public function test_auditee_can_upload_multiple_evidence_files(): void
    {
        Storage::fake('public');
        [$auditee, $assignment] = $this->auditeeAssignment();
        $instrument = $this->seedInstrument('S1-01');
        $assessment = SelfAssessment::query()->create([
            'assignment_id' => $assignment->id,
            'instrument_id' => $instrument->id,
            'target' => $instrument->target_kriteria,
            'status' => 'draft',
        ]);

        $this->actingAs($auditee)
            ->post("/auditee/evaluasi-diri/{$assessment->id}/evidences", [
                'nama_dokumen' => 'Dokumen VMTS',
                'jenis_dokumen' => 'PDF',
                'tipe_sumber' => 'file',
                'files' => [
                    UploadedFile::fake()->create('vmts.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->image('sosialisasi.png'),
                ],
                'tahun_dokumen' => 2026,
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseCount('evidences', 2);

        Evidence::query()->get()->each(function (Evidence $evidence): void {
            Storage::disk('public')->assertExists($evidence->path_file);
            $this->assertSame('S1-01', $evidence->instrumen_terkait);
        });
    }

    public function test_auditee_can_finalize_when_all_instruments_submitted(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $instrument = $this->seedInstrument('S1-01');
        SelfAssessment::query()->create([
            'assignment_id' => $assignment->id,
            'instrument_id' => $instrument->id,
            'target' => $instrument->target_kriteria,
            'status' => 'dikirim',
        ]);

        $this->actingAs($auditee)
            ->post("/auditee/evaluasi-diri/{$assignment->id}/finalize")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('self_assessments', [
            'assignment_id' => $assignment->id,
            'instrument_id' => $instrument->id,
            'status' => 'final',
        ]);
    }

    public function test_auditor_can_open_desk_evaluation_for_assigned_unit(): void
    {
        [$auditor, $assignment] = $this->deskEvaluationFixture();

        $this->actingAs($auditor)
            ->get('/auditor/desk-evaluation')
            ->assertOk()
            ->assertSee($assignment->unit->nama);

        $this->actingAs($auditor)
            ->get("/auditor/desk-evaluation/{$assignment->id}")
            ->assertOk()
            ->assertSee('Desk Evaluation Unit');

        $this->assertDatabaseCount('evaluations', 1);
    }

    public function test_auditor_cannot_open_unassigned_desk_evaluation(): void
    {
        [, $assignment] = $this->deskEvaluationFixture();
        $otherAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);

        $this->actingAs($otherAuditor)
            ->get("/auditor/desk-evaluation/{$assignment->id}")
            ->assertForbidden();
    }

    public function test_auditor_can_save_evaluation_and_mark_evidence_valid(): void
    {
        Storage::fake('public');
        [$auditor, $assignment, $assessment] = $this->deskEvaluationFixture();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $assignment->unit_id,
        ]);
        Evidence::query()->create([
            'self_assessment_id' => $assessment->id,
            'nama_dokumen' => 'Dokumen VMTS',
            'jenis_dokumen' => 'PDF',
            'tipe_sumber' => 'tautan',
            'url_tautan' => 'https://example.test/vmts',
            'uploaded_by' => $auditee->id,
            'instrumen_terkait' => $assessment->instrument->kode,
            'instrument_ids' => [$assessment->instrument_id],
        ]);

        $this->actingAs($auditor)->get("/auditor/desk-evaluation/{$assignment->id}");
        $evaluation = Evaluation::query()->firstOrFail();

        $this->actingAs($auditor)
            ->patch("/auditor/desk-evaluation/{$assignment->id}/evaluations/{$evaluation->id}", [
                'status_bukti' => 'valid',
                'catatan_auditor' => 'Bukti sesuai.',
                'usulan_temuan' => '1',
                'rekomendasi_awal' => 'Pertahankan dokumentasi.',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'status_bukti' => 'valid',
            'status_pemeriksaan' => 'berlangsung',
            'usulan_temuan' => true,
            'diperiksa_oleh' => $auditor->id,
        ]);
        $this->assertDatabaseHas('evidences', [
            'self_assessment_id' => $assessment->id,
            'status_verifikasi' => 'valid',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'desk_evaluation_diperbarui',
            'objek_tipe' => 'evaluation',
            'objek_id' => $evaluation->id,
        ]);
    }

    public function test_auditor_can_request_clarification(): void
    {
        Mail::fake();
        [$auditor, $assignment, $assessment] = $this->deskEvaluationFixture();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $assignment->unit_id,
            'email' => 'anggreaputrabagus@gmail.com',
        ]);
        $this->actingAs($auditor)->get("/auditor/desk-evaluation/{$assignment->id}");
        $evaluation = Evaluation::query()->firstOrFail();

        $this->actingAs($auditor)
            ->post("/auditor/desk-evaluation/{$assignment->id}/evaluations/{$evaluation->id}/clarification", [
                'catatan_auditor' => 'Mohon lengkapi bukti.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'status_pemeriksaan' => 'menunggu_klarifikasi',
            'status_bukti' => 'perlu_klarifikasi',
        ]);
        $this->assertDatabaseHas('self_assessments', [
            'id' => $assessment->id,
            'status' => 'perlu_klarifikasi',
        ]);
        $this->assertDatabaseHas('clarifications', [
            'assignment_id' => $assignment->id,
            'instrument_id' => $assessment->instrument_id,
            'dibuka_oleh' => $auditor->id,
            'status' => 'terbuka',
        ]);
        $this->assertDatabaseHas('clarification_messages', [
            'pengirim_id' => $auditor->id,
            'isi_pesan' => 'Mohon lengkapi bukti.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'klarifikasi_dibuat',
            'objek_tipe' => 'clarification',
            'is_read' => false,
        ]);
        Mail::assertSent(SiamiNotificationMail::class, function (SiamiNotificationMail $mail) use ($auditee): bool {
            return $mail->hasTo($auditee->email)
                && $mail->title === 'Klarifikasi Auditor'
                && str_contains($mail->body, 'Mohon lengkapi bukti.');
        });
    }

    public function test_auditor_and_auditee_can_use_clarification_thread(): void
    {
        [$auditor, $assignment, $assessment] = $this->deskEvaluationFixture();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $assignment->unit_id,
        ]);
        $clarification = $this->seedClarification($auditor, $assignment, $assessment);
        $this->actingAs($auditor)
            ->get('/auditor/klarifikasi')
            ->assertOk()
            ->assertSee($assignment->unit->nama);

        $this->actingAs($auditee)
            ->get('/auditee/klarifikasi-auditor')
            ->assertOk()
            ->assertSee('Mohon jelaskan bukti.');

        $this->actingAs($auditee)
            ->post("/auditee/klarifikasi-auditor/{$clarification->id}/messages", [
                'isi_pesan' => 'Bukti tambahan sudah kami unggah.',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('clarification_messages', [
            'clarification_id' => $clarification->id,
            'pengirim_id' => $auditee->id,
            'isi_pesan' => 'Bukti tambahan sudah kami unggah.',
        ]);
        $this->assertDatabaseHas('clarifications', [
            'id' => $clarification->id,
            'status' => 'dijawab',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditor->id,
            'tipe' => 'klarifikasi_dijawab',
            'objek_tipe' => 'clarification',
            'objek_id' => $clarification->id,
            'is_read' => false,
        ]);
    }

    public function test_unassigned_auditor_cannot_open_clarification(): void
    {
        [$auditor, $assignment, $assessment] = $this->deskEvaluationFixture();
        $clarification = $this->seedClarification($auditor, $assignment, $assessment);
        $otherAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);

        $this->actingAs($otherAuditor)
            ->get("/auditor/klarifikasi/{$clarification->id}")
            ->assertForbidden();
    }

    public function test_auditor_can_close_and_reopen_clarification(): void
    {
        Mail::fake();
        [$auditor, $assignment, $assessment] = $this->deskEvaluationFixture();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $assignment->unit_id,
            'email' => 'anggreaputrabagus@gmail.com',
        ]);
        $clarification = $this->seedClarification($auditor, $assignment, $assessment);
        $this->actingAs($auditor)
            ->patch("/auditor/klarifikasi/{$clarification->id}/finish")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('clarifications', [
            'id' => $clarification->id,
            'status' => 'selesai',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'klarifikasi_selesai',
            'objek_tipe' => 'clarification',
            'objek_id' => $clarification->id,
        ]);

        $this->actingAs($auditor)
            ->patch("/auditor/klarifikasi/{$clarification->id}/reopen")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('clarifications', [
            'id' => $clarification->id,
            'status' => 'dibuka_kembali',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'klarifikasi_dibuka_kembali',
            'objek_tipe' => 'clarification',
            'objek_id' => $clarification->id,
        ]);
        Mail::assertSent(SiamiNotificationMail::class, function (SiamiNotificationMail $mail) use ($auditee): bool {
            return $mail->hasTo($auditee->email)
                && $mail->title === 'Klarifikasi Ditandai Selesai';
        });
        Mail::assertSent(SiamiNotificationMail::class, function (SiamiNotificationMail $mail) use ($auditee): bool {
            return $mail->hasTo($auditee->email)
                && $mail->title === 'Klarifikasi Dibuka Kembali';
        });
    }

    public function test_auditee_can_upload_clarification_evidence(): void
    {
        Storage::fake('public');
        [$auditor, $assignment, $assessment] = $this->deskEvaluationFixture();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $assignment->unit_id,
        ]);
        $clarification = $this->seedClarification($auditor, $assignment, $assessment);

        $this->actingAs($auditee)
            ->post("/auditee/klarifikasi-auditor/{$clarification->id}/evidences", [
                'nama_dokumen' => 'Bukti Klarifikasi',
                'tipe_sumber' => 'file',
                'file' => UploadedFile::fake()->create('klarifikasi.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHas('status');

        $evidence = ClarificationEvidence::query()->firstOrFail();
        Storage::disk('public')->assertExists($evidence->path_file);
        $this->assertSame($auditee->id, $evidence->diunggah_oleh);
    }

    public function test_auditor_can_schedule_visit_and_notify_auditee(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $unit->id,
        ]);
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $this->actingAs($auditor)
            ->post("/auditor/visitasi/{$assignment->id}", [
                'tanggal' => '2026-06-15',
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '11:00',
                'tipe' => 'daring',
                'lokasi_atau_tautan' => 'https://meet.siami.test/visitasi',
                'agenda' => 'Wawancara pimpinan unit.',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('visits', [
            'assignment_id' => $assignment->id,
            'tanggal' => '2026-06-15 00:00:00',
            'status' => 'terjadwal',
            'tipe' => 'daring',
        ]);
        $this->assertDatabaseHas('audit_assignments', [
            'id' => $assignment->id,
            'jadwal_visitasi' => '2026-06-15 00:00:00',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'visitasi_dijadwalkan',
            'objek_tipe' => 'visit',
            'is_read' => false,
        ]);
    }

    public function test_unassigned_auditor_cannot_open_visit(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $otherAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);

        $this->actingAs($otherAuditor)
            ->get("/auditor/visitasi/{$assignment->id}")
            ->assertForbidden();
    }

    public function test_auditee_can_view_and_confirm_visit(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $visit = $this->seedVisit($assignment);

        $this->actingAs($auditee)
            ->get('/auditee/jadwal-visitasi')
            ->assertOk()
            ->assertSee($assignment->unit->nama);

        $this->actingAs($auditee)
            ->get("/auditee/jadwal-visitasi/{$visit->id}")
            ->assertOk()
            ->assertSee('Detail Jadwal Visitasi');

        $this->actingAs($auditee)
            ->patch("/auditee/jadwal-visitasi/{$visit->id}/confirm")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'konfirmasi_auditee' => true,
        ]);
    }

    public function test_auditee_can_upload_visit_attachment(): void
    {
        Storage::fake('public');
        [$auditee, $assignment] = $this->auditeeAssignment();
        $visit = $this->seedVisit($assignment);

        $this->actingAs($auditee)
            ->post("/auditee/jadwal-visitasi/{$visit->id}/attachments", [
                'nama_file' => 'Dokumen Tambahan Visitasi',
                'tipe_sumber' => 'file',
                'file' => UploadedFile::fake()->create('visitasi.pdf', 100, 'application/pdf'),
                'keterangan' => 'Dokumen pendukung wawancara.',
            ])
            ->assertSessionHas('status');

        $attachment = VisitAttachment::query()->firstOrFail();
        Storage::disk('public')->assertExists($attachment->path_file);
        $this->assertSame($auditee->id, $attachment->diunggah_oleh);
    }

    public function test_auditor_can_finish_visit_and_generate_minutes(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $visit = $this->seedVisit($assignment, [
            'tanggal' => '2026-06-01',
            'catatan_wawancara' => 'Wawancara berjalan lengkap.',
            'kesimpulan' => 'Visitasi selesai.',
        ]);
        $visit->participants()->create([
            'nama_peserta' => 'Auditor SIAMI',
            'jabatan' => 'Lead Auditor',
            'tipe' => 'auditor',
        ]);

        $this->actingAs($auditor)
            ->patch("/auditor/visitasi/{$assignment->id}/finish")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'status' => 'selesai',
        ]);

        $this->actingAs($auditor)
            ->get("/auditor/visitasi/{$assignment->id}/berita-acara")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_auditor_can_send_minutes_notification_and_auditee_approval_moves_status(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $auditor = $assignment->leadAuditor;
        $visit = $this->seedVisit($assignment, [
            'tanggal' => '2026-06-01',
            'status' => 'terjadwal',
        ]);
        $this->actingAs($auditor)
            ->post("/auditor/visitasi/{$assignment->id}/send-minutes")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'status' => 'selesai',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'berita_acara_dikirim',
            'objek_tipe' => 'visit',
            'objek_id' => $visit->id,
        ]);

        $this->actingAs($auditee)
            ->patch("/auditee/jadwal-visitasi/{$visit->id}/confirm")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'status' => 'berita_acara_disetujui',
            'konfirmasi_auditee' => true,
        ]);
    }

    public function test_auditor_can_create_and_finalize_finding(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $unit->id,
        ]);
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $instrument = $this->seedInstrument('S1-01');
        $this->actingAs($auditor)
            ->get('/auditor/temuan')
            ->assertOk()
            ->assertSee('Temuan');

        $this->actingAs($auditor)
            ->post('/auditor/temuan', $this->findingPayload($assignment, $instrument))
            ->assertRedirect();

        $finding = Finding::query()->firstOrFail();
        $this->assertSame('draft', $finding->status);

        $this->actingAs($auditor)
            ->patch("/auditor/temuan/{$finding->id}/finalize")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('findings', [
            'id' => $finding->id,
            'nomor_temuan' => 'AMI2026-TI-001',
            'status' => 'aktif',
            'difinalisasi_oleh' => $auditor->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'temuan_difinalisasi',
            'objek_tipe' => 'finding',
            'objek_id' => $finding->id,
        ]);
    }

    public function test_unassigned_auditor_cannot_open_finding(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument);
        $otherAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);

        $this->actingAs($otherAuditor)
            ->get("/auditor/temuan/{$finding->id}")
            ->assertForbidden();
    }

    public function test_active_finding_only_allows_limited_edit(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'aktif',
            'nomor_temuan' => 'AMI2026-TI-001',
            'waktu_finalisasi' => now(),
            'difinalisasi_oleh' => $auditor->id,
        ]);

        $this->actingAs($auditor)
            ->put("/auditor/temuan/{$finding->id}", [
                'kategori' => 'mayor',
                'target_penyelesaian' => '2026-09-15',
                'kondisi_aktual' => 'Upaya mengubah isi terkunci.',
            ])
            ->assertSessionHas('status');

        $finding->refresh();
        $this->assertSame('mayor', $finding->kategori);
        $this->assertSame('2026-09-15', $finding->target_penyelesaian->toDateString());
        $this->assertSame('Dokumen belum tersedia lengkap.', $finding->kondisi_aktual);
        $this->assertDatabaseHas('finding_status_histories', [
            'finding_id' => $finding->id,
            'field' => 'kategori',
        ]);
    }

    public function test_finding_becomes_overdue_when_target_passed(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'aktif',
            'nomor_temuan' => 'AMI2026-TI-001',
            'target_penyelesaian' => '2026-01-10',
        ]);

        $this->actingAs($auditor)
            ->get('/auditor/temuan')
            ->assertOk();

        $this->assertDatabaseHas('findings', [
            'id' => $finding->id,
            'status' => 'terlambat',
        ]);
    }

    public function test_auditor_can_cancel_finding_with_reason(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'aktif',
            'nomor_temuan' => 'AMI2026-TI-001',
        ]);

        $this->actingAs($auditor)
            ->patch("/auditor/temuan/{$finding->id}/cancel", [
                'alasan_pembatalan' => 'Duplikasi dengan temuan lain.',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('findings', [
            'id' => $finding->id,
            'status' => 'dibatalkan',
            'alasan_pembatalan' => 'Duplikasi dengan temuan lain.',
        ]);
    }

    public function test_auditor_can_print_finding_list_pdf(): void
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $instrument = $this->seedInstrument('S1-01');
        $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'aktif',
            'nomor_temuan' => 'AMI2026-TI-001',
        ]);

        $this->actingAs($auditor)
            ->get("/auditor/temuan/cetak?audit_period_id={$period->id}")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_auditee_can_create_upload_and_submit_follow_up(): void
    {
        Storage::fake('public');
        [$auditee, $assignment] = $this->auditeeAssignment();
        $auditor = $assignment->leadAuditor;
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'aktif',
            'nomor_temuan' => 'AMI2026-TI-001',
        ]);
        $this->actingAs($auditee)
            ->get('/auditee/temuan-tindak-lanjut')
            ->assertOk()
            ->assertSee('AMI2026-TI-001');

        $this->actingAs($auditee)
            ->post("/auditee/temuan-tindak-lanjut/{$finding->id}/follow-up", $this->followUpPayload())
            ->assertSessionHas('status');

        $followUp = FollowUp::query()->firstOrFail();
        $this->assertSame('draft', $followUp->status);

        $this->actingAs($auditee)
            ->post("/auditee/temuan-tindak-lanjut/{$finding->id}/follow-up/evidences", [
                'nama_dokumen' => 'Bukti Perbaikan',
                'jenis_dokumen' => 'PDF',
                'tipe_sumber' => 'file',
                'file' => UploadedFile::fake()->create('perbaikan.pdf', 100, 'application/pdf'),
                'tahun_dokumen' => 2026,
            ])
            ->assertSessionHas('status');

        $this->actingAs($auditee)
            ->post("/auditee/temuan-tindak-lanjut/{$finding->id}/follow-up/submit")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'status' => 'diajukan',
        ]);
        $this->assertDatabaseHas('findings', [
            'id' => $finding->id,
            'status' => 'menunggu_verifikasi',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditor->id,
            'tipe' => 'tindak_lanjut_diajukan',
            'objek_tipe' => 'follow_up',
            'objek_id' => $followUp->id,
        ]);
    }

    public function test_auditee_cannot_submit_follow_up_without_evidence(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $auditor = $assignment->leadAuditor;
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'aktif',
            'nomor_temuan' => 'AMI2026-TI-001',
        ]);

        FollowUp::query()->create([
            ...$this->followUpPayload(),
            'finding_id' => $finding->id,
            'assignment_id' => $assignment->id,
            'dibuat_oleh' => $auditee->id,
            'status' => 'draft',
        ]);

        $this->actingAs($auditee)
            ->post("/auditee/temuan-tindak-lanjut/{$finding->id}/follow-up/submit")
            ->assertSessionHas('warning');
    }

    public function test_auditor_can_approve_follow_up_and_close_finding(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $auditor = $assignment->leadAuditor;
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'menunggu_verifikasi',
            'nomor_temuan' => 'AMI2026-TI-001',
        ]);
        $followUp = $this->seedFollowUp($auditee, $finding, 'diajukan');
        $this->actingAs($auditor)
            ->get('/auditor/verifikasi-tindak-lanjut')
            ->assertOk()
            ->assertSee('AMI2026-TI-001');

        $this->actingAs($auditor)
            ->post("/auditor/verifikasi-tindak-lanjut/{$followUp->id}/verify", [
                'keputusan' => 'disetujui',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'status' => 'disetujui',
        ]);
        $this->assertDatabaseHas('findings', [
            'id' => $finding->id,
            'status' => 'ditutup',
        ]);
        $this->assertDatabaseHas('follow_up_verifications', [
            'follow_up_id' => $followUp->id,
            'keputusan' => 'disetujui',
            'verifikator_id' => $auditor->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'tindak_lanjut_diverifikasi',
            'objek_tipe' => 'follow_up_verification',
        ]);
    }

    public function test_auditor_can_request_follow_up_revision(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $auditor = $assignment->leadAuditor;
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'menunggu_verifikasi',
            'nomor_temuan' => 'AMI2026-TI-001',
        ]);
        $followUp = $this->seedFollowUp($auditee, $finding, 'diajukan');
        $this->actingAs($auditor)
            ->post("/auditor/verifikasi-tindak-lanjut/{$followUp->id}/verify", [
                'keputusan' => 'perlu_perbaikan',
                'catatan_verifikasi' => 'Bukti belum menunjukkan pelaksanaan.',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'status' => 'perlu_perbaikan',
        ]);
        $this->assertDatabaseHas('findings', [
            'id' => $finding->id,
            'status' => 'dalam_tindak_lanjut',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'tindak_lanjut_diverifikasi',
            'objek_tipe' => 'follow_up_verification',
        ]);
    }

    public function test_unassigned_auditor_cannot_verify_follow_up(): void
    {
        [$auditee, $assignment] = $this->auditeeAssignment();
        $auditor = $assignment->leadAuditor;
        $instrument = $this->seedInstrument('S1-01');
        $finding = $this->seedFinding($auditor, $assignment, $instrument, [
            'status' => 'menunggu_verifikasi',
            'nomor_temuan' => 'AMI2026-TI-001',
        ]);
        $followUp = $this->seedFollowUp($auditee, $finding, 'diajukan');
        $otherAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);

        $this->actingAs($otherAuditor)
            ->get("/auditor/verifikasi-tindak-lanjut/{$followUp->id}")
            ->assertForbidden();
    }

    public function test_auditor_can_finalize_desk_evaluation(): void
    {
        [$auditor, $assignment, $assessment] = $this->deskEvaluationFixture();
        $this->actingAs($auditor)->get("/auditor/desk-evaluation/{$assignment->id}");
        $evaluation = Evaluation::query()->firstOrFail();
        $evaluation->update([
            'status_bukti' => 'valid',
            'status_pemeriksaan' => 'berlangsung',
            'diperiksa_oleh' => $auditor->id,
        ]);

        $this->actingAs($auditor)
            ->post("/auditor/desk-evaluation/{$assignment->id}/finalize")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('evaluations', [
            'id' => $evaluation->id,
            'status_pemeriksaan' => 'final',
        ]);
        $this->assertDatabaseHas('self_assessments', [
            'id' => $assessment->id,
            'status' => 'final',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function periodPayload(User $admin, array $overrides = []): array
    {
        return [
            'nama' => 'AMI Contoh',
            'tahun_akademik' => '2026/2027',
            'jenis_audit' => 'akademik',
            'tanggal_mulai' => '2026-08-01',
            'batas_evaluasi_diri' => '2026-08-10',
            'batas_desk_evaluation' => '2026-08-20',
            'visitasi_mulai' => '2026-08-25',
            'visitasi_selesai' => '2026-08-30',
            'batas_tindak_lanjut' => '2026-09-10',
            'status' => 'draft',
            'created_by' => $admin->id,
            ...$overrides,
        ];
    }

    /**
     * @return array{0: User, 1: AuditPeriod, 2: Unit, 3: User, 4: User}
     */
    private function assignmentActors(): array
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $period = AuditPeriod::query()->create($this->periodPayload($admin, [
            'status' => 'aktif',
        ]));
        $unit = Unit::query()->create([
            'kode' => 'TI',
            'nama' => 'Program Studi Teknik Informatika',
            'jenis_unit' => 'prodi',
            'is_active' => true,
        ]);
        $leadAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);
        $memberAuditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);

        return [$admin, $period, $unit, $leadAuditor, $memberAuditor];
    }

    /**
     * @return array{0: User, 1: AuditAssignment}
     */
    private function auditeeAssignment(string $unitCode = 'TI', string $unitName = 'Program Studi Teknik Informatika'): array
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $period = AuditPeriod::query()->create($this->periodPayload($admin, [
            'status' => 'aktif',
            'batas_evaluasi_diri' => '2026-09-10',
            'batas_desk_evaluation' => '2026-09-20',
            'batas_tindak_lanjut' => '2026-10-10',
        ]));
        $unit = Unit::query()->create([
            'kode' => $unitCode,
            'nama' => $unitName,
            'jenis_unit' => 'prodi',
            'is_active' => true,
        ]);
        $auditee = User::factory()->create([
            'role' => UserRole::Auditee,
            'unit_id' => $unit->id,
        ]);
        $auditor = User::factory()->create([
            'role' => UserRole::Auditor,
            'unit_id' => null,
        ]);
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);

        return [$auditee, $assignment];
    }

    private function seedInstrument(string $code): Instrument
    {
        $standard = Standard::query()->firstOrCreate(
            ['kode' => 'S1'],
            ['nama' => 'Visi Misi', 'urutan' => 1, 'is_active' => true],
        );

        return Instrument::query()->create([
            'standard_id' => $standard->id,
            'kode' => $code,
            'pertanyaan' => 'Apakah dokumen VMTS tersedia?',
            'jenis_jawaban' => 'narasi',
            'target_kriteria' => 'Dokumen tersedia.',
            'bukti_diperlukan' => 'Dokumen VMTS.',
            'urutan' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * @return array{0: User, 1: AuditAssignment, 2: SelfAssessment}
     */
    private function deskEvaluationFixture(): array
    {
        [$admin, $period, $unit, $auditor] = $this->assignmentActors();
        $instrument = $this->seedInstrument('S1-01');
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $assessment = SelfAssessment::query()->create([
            'assignment_id' => $assignment->id,
            'instrument_id' => $instrument->id,
            'jawaban_naratif' => 'Dokumen VMTS tersedia.',
            'realisasi' => 'Tersedia',
            'target' => $instrument->target_kriteria,
            'status' => 'dikirim',
        ]);

        return [$auditor, $assignment, $assessment];
    }

    private function seedClarification(User $auditor, AuditAssignment $assignment, SelfAssessment $assessment): Clarification
    {
        $clarification = Clarification::query()->create([
            'assignment_id' => $assignment->id,
            'instrument_id' => $assessment->instrument_id,
            'dibuka_oleh' => $auditor->id,
            'status' => 'terbuka',
        ]);

        $clarification->messages()->create([
            'pengirim_id' => $auditor->id,
            'isi_pesan' => 'Mohon jelaskan bukti.',
        ]);

        return $clarification;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function seedVisit(AuditAssignment $assignment, array $overrides = []): Visit
    {
        return Visit::query()->create([
            'assignment_id' => $assignment->id,
            'tanggal' => '2026-06-15',
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'tipe' => 'lapangan',
            'lokasi_atau_tautan' => 'Ruang Rapat Unit',
            'agenda' => 'Wawancara dan observasi dokumen.',
            'status' => 'terjadwal',
            ...$overrides,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function findingPayload(AuditAssignment $assignment, Instrument $instrument): array
    {
        return [
            'assignment_id' => $assignment->id,
            'standard_id' => $instrument->standard_id,
            'instrument_id' => $instrument->id,
            'kategori' => 'minor',
            'prioritas' => 'sedang',
            'kondisi_aktual' => 'Dokumen belum tersedia lengkap.',
            'kriteria' => $instrument->target_kriteria,
            'bukti_objektif' => 'Hasil desk evaluation menunjukkan bukti belum lengkap.',
            'akar_masalah_awal' => 'Dokumentasi belum dikonsolidasikan.',
            'rekomendasi_auditor' => 'Lengkapi dokumen dan tetapkan PIC.',
            'target_penyelesaian' => '2026-08-15',
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function seedFinding(User $auditor, AuditAssignment $assignment, Instrument $instrument, array $overrides = []): Finding
    {
        return Finding::query()->create([
            ...$this->findingPayload($assignment, $instrument),
            'dibuat_oleh' => $auditor->id,
            'status' => 'draft',
            ...$overrides,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function followUpPayload(): array
    {
        return [
            'rencana_tindakan' => 'Melengkapi dokumen dan menetapkan jadwal monitoring.',
            'penanggung_jawab' => 'Kaprodi',
            'target_penyelesaian' => '2026-08-30',
            'indikator_keberhasilan' => 'Dokumen lengkap dan tersimpan di repositori unit.',
            'progres' => 'berlangsung',
            'kendala' => null,
            'catatan_auditee' => 'Perbaikan sedang berjalan.',
        ];
    }

    private function seedFollowUp(User $auditee, Finding $finding, string $status = 'draft'): FollowUp
    {
        return FollowUp::query()->create([
            ...$this->followUpPayload(),
            'finding_id' => $finding->id,
            'assignment_id' => $finding->assignment_id,
            'dibuat_oleh' => $auditee->id,
            'status' => $status,
        ]);
    }
}

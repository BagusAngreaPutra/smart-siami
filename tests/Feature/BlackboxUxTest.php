<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Evidence;
use App\Models\Finding;
use App\Models\Instrument;
use App\Models\Notification;
use App\Models\SelfAssessment;
use App\Models\Standard;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlackboxUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_blackbox_login_rejects_wrong_password_missing_fields_and_inactive_user(): void
    {
        $activeUser = User::factory()->create([
            'email' => 'auditor.blackbox@siami.test',
            'role' => UserRole::Auditor,
        ]);
        $inactiveUser = User::factory()->create([
            'email' => 'inactive.blackbox@siami.test',
            'role' => UserRole::Auditee,
            'is_active' => false,
        ]);

        $this->post('/login', [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->post('/login', [
            'email' => $activeUser->email,
            'password' => 'salah-password',
        ])->assertSessionHasErrors(['email']);

        $this->post('/login', [
            'email' => $inactiveUser->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_blackbox_role_boundaries_redirect_guests_and_return_403_for_wrong_roles(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $auditor = User::factory()->create(['role' => UserRole::Auditor]);
        $auditee = User::factory()->create(['role' => UserRole::Auditee]);

        $this->get('/auditor/dashboard')->assertRedirect('/login');

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/auditor/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/auditee/dashboard')->assertForbidden();

        $this->actingAs($auditor)->get('/auditor/dashboard')->assertOk();
        $this->actingAs($auditor)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($auditor)->get('/auditee/dashboard')->assertForbidden();

        $this->actingAs($auditee)->get('/auditee/dashboard')->assertOk();
        $this->actingAs($auditee)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($auditee)->get('/auditor/dashboard')->assertForbidden();
    }

    public function test_blackbox_admin_master_forms_reject_empty_and_duplicate_input(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $unit = $this->unit();
        $instrument = $this->instrument();

        $this->actingAs($admin)
            ->post('/admin/unit-pengguna/units', [])
            ->assertSessionHasErrors(['kode', 'nama', 'jenis_unit']);

        $this->actingAs($admin)
            ->post('/admin/unit-pengguna/units', [
                'kode' => strtolower($unit->kode),
                'nama' => 'Unit Duplikat',
                'jenis_unit' => 'prodi',
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['kode']);

        $this->actingAs($admin)
            ->post('/admin/standar-instrumen/standards', [])
            ->assertSessionHasErrors(['kode', 'nama']);

        $this->actingAs($admin)
            ->post('/admin/standar-instrumen/standards', [
                'kode' => strtolower($instrument->standard->kode),
                'nama' => 'Standar Duplikat',
                'urutan' => 1,
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['kode']);

        $this->actingAs($admin)
            ->post('/admin/standar-instrumen/instruments', [
                'standard_id' => $instrument->standard_id,
                'kode' => strtolower($instrument->kode),
                'pertanyaan' => 'Pertanyaan duplikat',
                'jenis_jawaban' => 'narasi',
                'target_kriteria' => 'Target',
                'bukti_diperlukan' => 'Bukti',
                'urutan' => 1,
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['kode']);
    }

    public function test_blackbox_admin_period_assignment_and_user_forms_fail_clearly_when_required_data_is_missing(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post('/admin/periode-audit', [])
            ->assertSessionHasErrors([
                'nama',
                'tahun_akademik',
                'jenis_audit',
                'tanggal_mulai',
                'batas_evaluasi_diri',
                'batas_desk_evaluation',
                'batas_tindak_lanjut',
            ]);

        $this->actingAs($admin)
            ->post('/admin/penugasan-audit', [])
            ->assertSessionHasErrors(['audit_period_id', 'unit_id', 'lead_auditor_id']);

        $this->actingAs($admin)
            ->post('/admin/unit-pengguna/users', [
                'name' => 'Auditee Tanpa Unit',
                'email' => 'auditee-no-unit@siami.test',
                'role' => 'auditee',
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['unit_id']);
    }

    public function test_blackbox_auditee_self_assessment_blocks_invalid_numeric_answers_and_incomplete_finalization(): void
    {
        [$auditee, $assignment] = $this->assignmentFixture();
        $instrument = $this->instrument('S1-01', 'angka');
        $assessment = SelfAssessment::query()->create([
            'assignment_id' => $assignment->id,
            'instrument_id' => $instrument->id,
            'target' => $instrument->target_kriteria,
            'status' => 'belum_diisi',
        ]);

        $this->actingAs($auditee)
            ->patch("/auditee/evaluasi-diri/{$assessment->id}/submit", [
                'jawaban_naratif' => 'Sudah berjalan',
                'realisasi' => 'bukan angka',
            ])
            ->assertSessionHasErrors(['realisasi']);

        $this->assertDatabaseHas('self_assessments', [
            'id' => $assessment->id,
            'status' => 'belum_diisi',
        ]);

        $this->actingAs($auditee)
            ->post("/auditee/evaluasi-diri/{$assignment->id}/finalize")
            ->assertSessionHas('warning');
    }

    public function test_blackbox_auditee_document_upload_rejects_missing_or_unsupported_files(): void
    {
        Storage::fake('public');
        [$auditee, $assignment] = $this->assignmentFixture();
        $instrument = $this->instrument();

        $this->actingAs($auditee)
            ->post('/auditee/bukti-dokumen', [
                'assignment_id' => $assignment->id,
                'nama_dokumen' => 'Bukti Tanpa File',
                'instrument_ids' => [$instrument->id],
                'tipe_sumber' => 'file',
            ])
            ->assertSessionHasErrors(['file']);

        $this->actingAs($auditee)
            ->post('/auditee/bukti-dokumen', [
                'assignment_id' => $assignment->id,
                'nama_dokumen' => 'File Tidak Didukung',
                'instrument_ids' => [$instrument->id],
                'tipe_sumber' => 'file',
                'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors(['file']);

        $this->assertDatabaseCount('evidences', 0);
    }

    public function test_blackbox_auditor_visit_keeps_future_schedule_as_scheduled_and_notifies_auditee(): void
    {
        [$auditee, $assignment, $auditor] = $this->assignmentFixture();
        $futureDate = now()->addDays(5)->toDateString();

        $this->actingAs($auditor)
            ->post("/auditor/visitasi/{$assignment->id}", [
                'tanggal' => $futureDate,
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '11:00',
                'tipe' => 'daring',
                'lokasi_atau_tautan' => 'https://meet.siami.test/visitasi',
                'agenda' => 'Wawancara dan observasi dokumen.',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('visits', [
            'assignment_id' => $assignment->id,
            'status' => 'terjadwal',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $auditee->id,
            'tipe' => 'visitasi_dijadwalkan',
            'is_read' => false,
        ]);

        $this->actingAs($auditor)
            ->patch("/auditor/visitasi/{$assignment->id}/finish")
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('visits', [
            'assignment_id' => $assignment->id,
            'status' => 'terjadwal',
        ]);
    }

    public function test_blackbox_auditor_finding_form_rejects_missing_fields_and_cancel_requires_reason(): void
    {
        [, $assignment, $auditor] = $this->assignmentFixture();
        $instrument = $this->instrument();

        $this->actingAs($auditor)
            ->post('/auditor/temuan', [])
            ->assertSessionHasErrors([
                'assignment_id',
                'standard_id',
                'instrument_id',
                'kategori',
                'prioritas',
                'kondisi_aktual',
                'kriteria',
                'bukti_objektif',
                'rekomendasi_auditor',
                'target_penyelesaian',
            ]);

        $finding = Finding::query()->create([
            'assignment_id' => $assignment->id,
            'standard_id' => $instrument->standard_id,
            'instrument_id' => $instrument->id,
            'kategori' => 'minor',
            'prioritas' => 'sedang',
            'kondisi_aktual' => 'Dokumen belum lengkap.',
            'kriteria' => 'Dokumen wajib tersedia.',
            'bukti_objektif' => 'Daftar dokumen kosong.',
            'rekomendasi_auditor' => 'Lengkapi dokumen.',
            'target_penyelesaian' => now()->addDays(10),
            'status' => 'draft',
            'dibuat_oleh' => $auditor->id,
        ]);

        $this->actingAs($auditor)
            ->patch("/auditor/temuan/{$finding->id}/cancel", [])
            ->assertSessionHasErrors(['alasan_pembatalan']);
    }

    public function test_blackbox_notification_open_marks_only_the_owner_notification_as_read(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Auditee]);
        $otherUser = User::factory()->create(['role' => UserRole::Auditee]);
        $ownerNotification = Notification::sendNotification(
            $owner->id,
            'pengingat_manual',
            'Pengingat',
            'Mohon cek data audit.',
            '/auditee/dashboard',
            'manual',
            1,
        );
        $otherNotification = Notification::sendNotification(
            $otherUser->id,
            'pengingat_manual',
            'Pengingat Orang Lain',
            'Ini bukan milik user aktif.',
            '/auditee/dashboard',
            'manual',
            2,
        );

        $this->actingAs($owner)
            ->get("/notifications/{$ownerNotification->id}")
            ->assertRedirect('/auditee/dashboard');

        $this->actingAs($owner)
            ->get("/notifications/{$otherNotification->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('notifications', [
            'id' => $ownerNotification->id,
            'is_read' => true,
        ]);
        $this->assertDatabaseHas('notifications', [
            'id' => $otherNotification->id,
            'is_read' => false,
        ]);
    }

    private function unit(array $overrides = []): Unit
    {
        return Unit::query()->create([
            'kode' => 'BBX',
            'nama' => 'Program Studi Blackbox',
            'jenis_unit' => 'prodi',
            'fakultas_induk' => 'Fakultas Pengujian',
            'nama_pimpinan' => 'Kaprodi Blackbox',
            'email' => 'blackbox@siami.test',
            'phone' => '0800000000',
            'is_active' => true,
            ...$overrides,
        ]);
    }

    /**
     * @return array{0: User, 1: AuditAssignment, 2: User, 3: Unit, 4: AuditPeriod}
     */
    private function assignmentFixture(): array
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $unit = $this->unit(['kode' => 'BB'.random_int(10, 99)]);
        $auditor = User::factory()->create(['role' => UserRole::Auditor, 'unit_id' => null]);
        $auditee = User::factory()->create(['role' => UserRole::Auditee, 'unit_id' => $unit->id]);
        $period = AuditPeriod::query()->create([
            'nama' => 'AMI Blackbox',
            'tahun_akademik' => '2026/2027',
            'jenis_audit' => 'reguler',
            'tanggal_mulai' => now()->subDay(),
            'batas_evaluasi_diri' => now()->addDays(20),
            'batas_desk_evaluation' => now()->addDays(30),
            'visitasi_mulai' => now()->addDays(3),
            'visitasi_selesai' => now()->addDays(10),
            'batas_tindak_lanjut' => now()->addDays(45),
            'status' => 'aktif',
            'created_by' => $admin->id,
        ]);
        $assignment = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $unit->id,
            'lead_auditor_id' => $auditor->id,
            'status' => 'aktif',
        ]);
        $assignment->auditors()->attach($auditor->id, ['peran_dalam_tim' => 'lead']);

        return [$auditee, $assignment, $auditor, $unit, $period];
    }

    private function instrument(string $code = 'S1-01', string $answerType = 'narasi'): Instrument
    {
        $standard = Standard::query()->firstOrCreate(
            ['kode' => 'S1'],
            [
                'nama' => 'Visi Misi',
                'deskripsi' => 'Standar uji blackbox.',
                'target' => 'Target standar.',
                'is_active' => true,
                'urutan' => 1,
            ],
        );

        return Instrument::query()->create([
            'kode' => $code,
            'standard_id' => $standard->id,
            'nama_indikator' => 'Indikator Blackbox',
            'pertanyaan' => 'Apakah proses berjalan sesuai standar?',
            'jenis_jawaban' => $answerType,
            'target_kriteria' => 'Target harus terpenuhi.',
            'bobot' => 1,
            'panduan_pengisian' => 'Isi sesuai kondisi nyata.',
            'bukti_diperlukan' => 'Dokumen pendukung.',
            'is_active' => true,
            'urutan' => 1,
        ]);
    }
}

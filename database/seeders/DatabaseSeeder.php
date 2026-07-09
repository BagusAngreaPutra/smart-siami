<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\Clarification;
use App\Models\ClarificationMessage;
use App\Models\Evaluation;
use App\Models\Evidence;
use App\Models\Finding;
use App\Models\FindingCategory;
use App\Models\FollowUp;
use App\Models\FollowUpVerification;
use App\Models\Instrument;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\SelfAssessment;
use App\Models\Setting;
use App\Models\Standard;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->resetSimulationData();

        $units = $this->seedUnits();
        $users = $this->seedUsers($units);
        $this->seedSettings();
        $this->seedFindingCategories();
        $this->seedNotificationTemplates();

        $standards = $this->seedStandards();
        $instruments = $this->seedInstruments($standards);
        $periods = $this->seedPeriods($users['admin']);
        $assignments = $this->seedAssignments($periods['active'], $units, $users);

        $assessments = $this->seedSelfAssessments($assignments, $instruments, $users);
        $this->seedEvaluations($assignments, $instruments, $assessments, $users);
        $this->seedClarifications($assignments, $instruments, $users);
        $this->seedVisits($assignments, $users);
        $this->seedFindingsAndFollowUps($assignments, $standards, $instruments, $users);
        $this->seedNotifications($assignments, $users);
    }

    private function resetSimulationData(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'follow_up_verifications',
            'follow_ups',
            'finding_status_histories',
            'findings',
            'visit_attachments',
            'visit_participants',
            'visits',
            'clarification_evidences',
            'clarification_messages',
            'clarifications',
            'evaluations',
            'evidences',
            'self_assessments',
            'assignment_auditors',
            'audit_assignments',
            'audit_periods',
            'notifications',
            'instruments',
            'standards',
            'notification_templates',
            'finding_categories',
            'settings',
            'users',
            'units',
        ] as $table) {
            DB::table($table)->delete();
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return array<string, Unit>
     */
    private function seedUnits(): array
    {
        return [
            'lpm' => Unit::query()->create([
                'kode' => 'LPM',
                'nama' => 'Lembaga Penjaminan Mutu',
                'jenis_unit' => 'unit_kerja',
                'fakultas_induk' => null,
                'nama_pimpinan' => 'Dr. Maya Rachmawati, M.M.',
                'email' => 'lpm@jdsuniversity.ac.id',
                'phone' => '021-555-0101',
                'is_active' => true,
            ]),
            'fti' => Unit::query()->create([
                'kode' => 'FTI',
                'nama' => 'Fakultas Teknologi Informasi',
                'jenis_unit' => 'fakultas',
                'fakultas_induk' => null,
                'nama_pimpinan' => 'Prof. Dr. Hendra Wiratama',
                'email' => 'fti@jdsuniversity.ac.id',
                'phone' => '021-555-0201',
                'is_active' => true,
            ]),
            'ti' => Unit::query()->create([
                'kode' => 'TI',
                'nama' => 'Program Studi Teknik Informatika',
                'jenis_unit' => 'prodi',
                'fakultas_induk' => 'Fakultas Teknologi Informasi',
                'nama_pimpinan' => 'Dr. Rina Puspitasari, S.Kom., M.T.',
                'email' => 'ti@jdsuniversity.ac.id',
                'phone' => '021-555-0211',
                'is_active' => true,
            ]),
            'si' => Unit::query()->create([
                'kode' => 'SI',
                'nama' => 'Program Studi Sistem Informasi',
                'jenis_unit' => 'prodi',
                'fakultas_induk' => 'Fakultas Teknologi Informasi',
                'nama_pimpinan' => 'Dr. Farah Nabila, S.Kom., M.M.S.I.',
                'email' => 'si@jdsuniversity.ac.id',
                'phone' => '021-555-0212',
                'is_active' => true,
            ]),
            'mnj' => Unit::query()->create([
                'kode' => 'MNJ',
                'nama' => 'Program Studi Manajemen',
                'jenis_unit' => 'prodi',
                'fakultas_induk' => 'Fakultas Ekonomi dan Bisnis',
                'nama_pimpinan' => 'Dr. Aditya Pranata, M.M.',
                'email' => 'manajemen@jdsuniversity.ac.id',
                'phone' => '021-555-0311',
                'is_active' => true,
            ]),
        ];
    }

    /**
     * @param  array<string, Unit>  $units
     * @return array<string, User>
     */
    private function seedUsers(array $units): array
    {
        return [
            'admin' => User::query()->create([
                'name' => 'Dr. Maya Rachmawati',
                'nip_nidn' => '197805122005012001',
                'email' => 'cedokajaib12@gmail.com',
                'phone' => '0812-1000-0101',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'unit_id' => $units['lpm']->id,
                'is_active' => true,
            ]),
            'auditor_lead' => User::query()->create([
                'name' => 'Dr. Rafi Nugroho',
                'nip_nidn' => '198204172009121003',
                'email' => 'angreaputrabagus@gmail.com',
                'phone' => '0812-2000-0201',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditor,
                'unit_id' => $units['lpm']->id,
                'is_active' => true,
            ]),
            'auditor_member' => User::query()->create([
                'name' => 'Dr. Annisa Kartika',
                'nip_nidn' => '198611022014042002',
                'email' => 'annisa.kartika@jdsuniversity.ac.id',
                'phone' => '0812-2000-0202',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditor,
                'unit_id' => $units['fti']->id,
                'is_active' => true,
            ]),
            'auditee_ti' => User::query()->create([
                'name' => 'Dr. Rina Puspitasari',
                'nip_nidn' => '198901152015032001',
                'email' => 'anggreaputrabagus@gmail.com',
                'phone' => '0812-3000-0301',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditee,
                'unit_id' => $units['ti']->id,
                'is_active' => true,
            ]),
            'auditee_si' => User::query()->create([
                'name' => 'Dr. Farah Nabila',
                'nip_nidn' => '198710252016042001',
                'email' => 'farah.nabila@jdsuniversity.ac.id',
                'phone' => '0812-3000-0302',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditee,
                'unit_id' => $units['si']->id,
                'is_active' => true,
            ]),
            'auditee_mnj' => User::query()->create([
                'name' => 'Dr. Aditya Pranata',
                'nip_nidn' => '198003192010011001',
                'email' => 'aditya.pranata@jdsuniversity.ac.id',
                'phone' => '0812-3000-0303',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditee,
                'unit_id' => $units['mnj']->id,
                'is_active' => true,
            ]),
        ];
    }

    private function seedSettings(): void
    {
        foreach ([
            'nama_institusi' => 'Universitas JDS Nusantara',
            'logo_path' => null,
            'nama_lpm' => 'Lembaga Penjaminan Mutu',
            'email_lpm' => 'lpm@jdsuniversity.ac.id',
            'max_file_size_mb' => '10',
            'allowed_file_types' => 'pdf,docx,xlsx,jpg,png',
            'report_paper_size' => 'A4',
            'report_orientation' => 'portrait',
            'report_margin_top_cm' => '1.8',
            'report_margin_right_cm' => '1.6',
            'report_margin_bottom_cm' => '1.8',
            'report_margin_left_cm' => '1.6',
            'report_font_family' => 'Arial',
            'report_font_size' => '11',
            'report_line_height' => '1.35',
            'report_table_density' => 'compact',
            'report_show_visual_summary' => '1',
            'report_letterhead_mode' => 'default',
            'report_letterhead_institution' => 'UNIVERSITAS JDS NUSANTARA',
            'report_letterhead_unit' => 'LEMBAGA PENJAMINAN MUTU',
            'report_letterhead_address' => 'Jl. Pendidikan Digital No. 12, Kota Bandung 40132',
            'report_letterhead_contact' => 'Telp. (022) 555-0199 | Email: lpm@jdsuniversity.ac.id | www.jdsuniversity.ac.id',
            'report_letterhead_logo_width' => '88',
            'report_letterhead_institution_font_size' => '16',
            'report_letterhead_unit_font_size' => '14',
            'report_letterhead_address_font_size' => '11',
            'report_letterhead_institution_bold' => '1',
            'report_letterhead_unit_bold' => '1',
            'report_letterhead_address_bold' => '0',
            'email_notifications_enabled' => '1',
        ] as $key => $value) {
            Setting::query()->create(['key' => $key, 'value' => $value]);
        }
    }

    private function seedFindingCategories(): void
    {
        foreach ([
            ['nama' => 'Observasi', 'warna_hex' => '#64748B', 'urutan' => 1],
            ['nama' => 'Peluang Peningkatan', 'warna_hex' => '#0E6656', 'urutan' => 2],
            ['nama' => 'Minor', 'warna_hex' => '#D9A441', 'urutan' => 3],
            ['nama' => 'Mayor', 'warna_hex' => '#C7645A', 'urutan' => 4],
        ] as $category) {
            FindingCategory::query()->create([...$category, 'is_active' => true]);
        }
    }

    private function seedNotificationTemplates(): void
    {
        foreach ([
            'periode_dibuka' => ['Periode Audit Dibuka', 'Periode audit {nama_periode} telah dibuka.'],
            'penugasan_dibuat' => ['Penugasan Audit', 'Anda terhubung dengan penugasan audit unit {nama_unit} pada periode {nama_periode}.'],
            'evaluasi_diri_dikirim' => ['Evaluasi Diri Dikirim', 'Evaluasi diri unit {nama_unit} telah dikirim.'],
            'klarifikasi_dibuat' => ['Klarifikasi Auditor', 'Auditor meminta klarifikasi untuk unit {nama_unit}.'],
            'klarifikasi_dijawab' => ['Klarifikasi Dijawab', 'Auditee telah menjawab klarifikasi auditor.'],
            'visitasi_dijadwalkan' => ['Jadwal Visitasi', 'Visitasi unit {nama_unit} telah dijadwalkan.'],
            'berita_acara_dikirim' => ['Berita Acara Visitasi', 'Berita acara visitasi unit {nama_unit} telah dikirim.'],
            'temuan_difinalisasi' => ['Temuan Audit Baru', 'Temuan {nomor_temuan} telah difinalisasi.'],
            'tindak_lanjut_diajukan' => ['Tindak Lanjut Diajukan', 'Tindak lanjut temuan {nomor_temuan} menunggu verifikasi.'],
            'tindak_lanjut_diverifikasi' => ['Tindak Lanjut Diverifikasi', 'Keputusan tindak lanjut temuan {nomor_temuan} telah tersedia.'],
            'pengingat_batas_waktu' => ['Pengingat Batas Waktu', 'Batas waktu {batas_waktu} untuk unit {nama_unit} sudah dekat.'],
            'pengingat_manual' => ['Pengingat dari Admin', 'Mohon segera melengkapi proses audit unit {nama_unit}.'],
        ] as $type => [$title, $body]) {
            NotificationTemplate::query()->create([
                'tipe' => $type,
                'judul_template' => $title,
                'isi_template' => $body,
            ]);
        }
    }

    /**
     * @return array<string, Standard>
     */
    private function seedStandards(): array
    {
        $records = [
            'vmts' => ['K1', 'Visi, Misi, Tujuan, dan Strategi', 'Kesesuaian VMTS dengan arah pengembangan program studi dan kebutuhan pemangku kepentingan.', 'visi misi tujuan strategi', 1],
            'governance' => ['K2', 'Tata Pamong, Tata Kelola, dan Kerja Sama', 'Efektivitas tata pamong, sistem penjaminan mutu, dan kerja sama.', 'tata pamong tata kelola kerja sama', 2],
            'students' => ['K3', 'Mahasiswa', 'Kualitas input mahasiswa, layanan kemahasiswaan, prestasi, dan kepuasan.', 'mahasiswa layanan prestasi', 3],
            'hr' => ['K4', 'Sumber Daya Manusia', 'Kecukupan, kompetensi, pengembangan, dan rekognisi dosen serta tenaga kependidikan.', 'sumber daya manusia', 4],
            'education' => ['K5', 'Pendidikan', 'Kurikulum, proses pembelajaran, asesmen, dan suasana akademik.', 'pendidikan kurikulum pembelajaran', 5],
        ];

        $standards = [];

        foreach ($records as $key => [$code, $name, $description, $target, $order]) {
            $standards[$key] = Standard::query()->create([
                'kode' => $code,
                'nama' => $name,
                'deskripsi' => $description,
                'target' => $target,
                'is_active' => true,
                'urutan' => $order,
            ]);
        }

        return $standards;
    }

    /**
     * @param  array<string, Standard>  $standards
     * @return array<string, Instrument>
     */
    private function seedInstruments(array $standards): array
    {
        $rows = [
            'vmts_1' => [$standards['vmts'], 'BAN-PT-S1-1.1', 'BAN-PT S1', '1.1', '1.1.1', '1.1.1.1', '1.1', 'visi misi tujuan strategi', 'Program studi memiliki visi keilmuan yang memuat keunikan program studi dan sesuai perkembangan IPTEKS.', 1],
            'vmts_2' => [$standards['vmts'], 'BAN-PT-S1-1.2', 'BAN-PT S1', '1.1', '1.1.2', '1.1.2.1', '1.2', 'sosialisasi VMTS', 'VMTS disosialisasikan secara konsisten kepada dosen, mahasiswa, tenaga kependidikan, alumni, dan pengguna lulusan.', 2],
            'gov_1' => [$standards['governance'], 'BAN-PT-S1-2.1', 'BAN-PT S1', '2.1', '2.1.1', '2.1.1.1', '2.1', 'tata pamong', 'Program studi memiliki struktur organisasi, uraian tugas, dan mekanisme pengambilan keputusan yang terdokumentasi.', 3],
            'gov_2' => [$standards['governance'], 'BAN-PT-S1-2.2', 'BAN-PT S1', '2.2', '2.2.1', '2.2.1.1', '2.2', 'siklus PPEPP', 'Program studi menjalankan siklus penetapan, pelaksanaan, evaluasi, pengendalian, dan peningkatan standar secara berkala.', 4],
            'students_1' => [$standards['students'], 'BAN-PT-S1-3.1', 'BAN-PT S1', '3.1', '3.1.1', '3.1.1.1', '3.1', 'layanan mahasiswa', 'Program studi menyediakan layanan akademik, kemahasiswaan, karier, dan konseling yang mudah diakses mahasiswa.', 5],
            'hr_1' => [$standards['hr'], 'BAN-PT-S1-4.1', 'BAN-PT S1', '4.1', '4.1.1', '4.1.1.1', '4.1', 'kecukupan dosen', 'Program studi memiliki dosen tetap dengan kualifikasi dan bidang keahlian yang sesuai dengan kompetensi lulusan.', 6],
            'education_1' => [$standards['education'], 'BAN-PT-S1-5.1', 'BAN-PT S1', '5.1', '5.1.1', '5.1.1.1', '5.1', 'kurikulum OBE', 'Kurikulum disusun berbasis capaian pembelajaran lulusan dan dievaluasi bersama pemangku kepentingan.', 7],
            'education_2' => [$standards['education'], 'BAN-PT-S1-5.2', 'BAN-PT S1', '5.2', '5.2.1', '5.2.1.1', '5.2', 'asesmen pembelajaran', 'Metode asesmen pembelajaran selaras dengan CPL dan terdokumentasi dalam RPS serta rubrik penilaian.', 8],
        ];

        $instruments = [];

        foreach ($rows as $key => [$standard, $code, $body, $ss, $ikss, $ik, $indicatorCode, $universityStandard, $question, $order]) {
            $instruments[$key] = Instrument::query()->create([
                'standard_id' => $standard->id,
                'kode' => $code,
                'accreditation_body' => $body,
                'sasaran_strategi_kode' => $ss,
                'ikss_kode' => $ikss,
                'indikator_kegiatan_kode' => $ik,
                'kode_indikator_akreditasi' => $indicatorCode,
                'standar_universitas' => $universityStandard,
                'nama_indikator' => $universityStandard,
                'pertanyaan' => $question,
                'jenis_jawaban' => 'narasi',
                'target_kriteria' => $question,
                'matriks_skor' => [
                    '4' => 'Dokumen lengkap, mutakhir, diterapkan konsisten, dievaluasi, dan ditindaklanjuti.',
                    '3' => 'Dokumen tersedia dan diterapkan, namun evaluasi atau tindak lanjut belum konsisten.',
                    '2' => 'Dokumen tersedia sebagian dan implementasi belum merata.',
                    '1' => 'Dokumen tidak tersedia atau belum dapat ditunjukkan.',
                ],
                'bobot' => 1,
                'panduan_pengisian' => 'Jelaskan kondisi aktual secara ringkas dan tautkan bukti pendukung yang relevan.',
                'bukti_diperlukan' => 'Dokumen kebijakan, SK, berita acara, laporan evaluasi, tautan sistem, atau bukti kegiatan.',
                'is_active' => true,
                'urutan' => $order,
                'sumber_template' => 'Dataset simulasi AMI lapangan',
                'imported_at' => now(),
            ]);
        }

        return $instruments;
    }

    /**
     * @return array<string, AuditPeriod>
     */
    private function seedPeriods(User $admin): array
    {
        return [
            'active' => AuditPeriod::query()->create([
                'nama' => 'AMI Semester Genap 2025/2026',
                'tahun_akademik' => '2025/2026',
                'jenis_audit' => 'akademik',
                'tanggal_mulai' => '2026-07-01',
                'batas_evaluasi_diri' => '2026-07-20',
                'batas_desk_evaluation' => '2026-07-31',
                'visitasi_mulai' => '2026-08-04',
                'visitasi_selesai' => '2026-08-08',
                'batas_tindak_lanjut' => '2026-09-15',
                'status' => 'aktif',
                'catatan' => 'Simulasi audit mutu internal akademik untuk dua program studi prioritas.',
                'created_by' => $admin->id,
            ]),
            'draft' => AuditPeriod::query()->create([
                'nama' => 'AMI Semester Ganjil 2026/2027',
                'tahun_akademik' => '2026/2027',
                'jenis_audit' => 'reguler',
                'tanggal_mulai' => '2026-11-02',
                'batas_evaluasi_diri' => '2026-11-20',
                'batas_desk_evaluation' => '2026-12-04',
                'visitasi_mulai' => '2026-12-08',
                'visitasi_selesai' => '2026-12-12',
                'batas_tindak_lanjut' => '2027-01-31',
                'status' => 'draft',
                'catatan' => 'Draft periode berikutnya untuk uji duplikasi dan pengarsipan.',
                'created_by' => $admin->id,
            ]),
        ];
    }

    /**
     * @param  array<string, Unit>  $units
     * @param  array<string, User>  $users
     * @return array<string, AuditAssignment>
     */
    private function seedAssignments(AuditPeriod $period, array $units, array $users): array
    {
        $ti = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $units['ti']->id,
            'lead_auditor_id' => $users['auditor_lead']->id,
            'catatan_penugasan' => 'Fokus audit pada implementasi PPEPP, kurikulum OBE, dan bukti tindak lanjut rapat tinjauan manajemen.',
            'tanggal_desk_evaluation' => '2026-07-24',
            'jadwal_visitasi' => '2026-08-05',
            'status' => 'aktif',
        ]);
        $ti->auditors()->sync([
            $users['auditor_lead']->id => ['peran_dalam_tim' => 'lead'],
            $users['auditor_member']->id => ['peran_dalam_tim' => 'anggota'],
        ]);

        $si = AuditAssignment::query()->create([
            'audit_period_id' => $period->id,
            'unit_id' => $units['si']->id,
            'lead_auditor_id' => $users['auditor_member']->id,
            'catatan_penugasan' => 'Fokus audit pada layanan mahasiswa, evaluasi kurikulum, dan dokumentasi kerja sama.',
            'tanggal_desk_evaluation' => '2026-07-26',
            'jadwal_visitasi' => null,
            'status' => 'aktif',
        ]);
        $si->auditors()->sync([
            $users['auditor_member']->id => ['peran_dalam_tim' => 'lead'],
        ]);

        return ['ti' => $ti, 'si' => $si];
    }

    /**
     * @param  array<string, AuditAssignment>  $assignments
     * @param  array<string, Instrument>  $instruments
     * @param  array<string, User>  $users
     * @return array<string, SelfAssessment>
     */
    private function seedSelfAssessments(array $assignments, array $instruments, array $users): array
    {
        $records = [
            'ti_vmts_1' => [$assignments['ti'], $instruments['vmts_1'], 'dikirim', 'Visi keilmuan Prodi Teknik Informatika telah ditetapkan melalui SK Dekan dan mengarah pada kecerdasan buatan terapan.', 'SK Visi Keilmuan 2024 tersedia; sosialisasi dilakukan pada rapat awal semester dan laman prodi.', null, 'Belum semua mata kuliah mencantumkan pemetaan ke visi keilmuan.', 'Melengkapi pemetaan visi keilmuan pada dokumen RPS semester berikutnya.'],
            'ti_vmts_2' => [$assignments['ti'], $instruments['vmts_2'], 'final', 'VMTS disosialisasikan melalui website, handbook mahasiswa, rapat dosen, dan kegiatan orientasi mahasiswa baru.', 'Terdapat berita acara sosialisasi, materi orientasi, dan tautan website aktif.', null, 'Kanal alumni belum terdokumentasi lengkap.', 'Menambahkan dokumentasi sosialisasi VMTS pada forum alumni.'],
            'ti_gov_1' => [$assignments['ti'], $instruments['gov_1'], 'perlu_klarifikasi', 'Struktur organisasi tersedia, tetapi beberapa uraian tugas koordinator laboratorium masih dalam proses pembaruan.', 'SK struktur organisasi tersedia; draft uraian tugas laboratorium sedang direvisi.', 'Uraian tugas koordinator lab belum disahkan.', 'Dokumen implementasi belum lengkap untuk seluruh fungsi.', 'Finalisasi uraian tugas laboratorium sebelum visitasi.'],
            'ti_gov_2' => [$assignments['ti'], $instruments['gov_2'], 'dikirim', 'Siklus PPEPP dilaksanakan melalui rapat evaluasi kurikulum, monev pembelajaran, dan rapat tindak lanjut.', 'Laporan monev semester ganjil tersedia dan dibahas pada rapat prodi.', null, 'Sebagian tindak lanjut belum memiliki PIC dan target waktu.', 'Menyusun matriks tindak lanjut PPEPP dengan PIC dan batas waktu.'],
            'ti_students_1' => [$assignments['ti'], $instruments['students_1'], 'draft', 'Layanan akademik, konseling, dan karier tersedia melalui dosen wali, CDC, dan helpdesk fakultas.', 'Data layanan mahasiswa tersedia sebagian.', 'Rekap kepuasan layanan karier belum selesai.', 'Instrumen kepuasan belum rutin dianalisis per prodi.', 'Melengkapi rekap kepuasan layanan mahasiswa.'],
            'si_vmts_1' => [$assignments['si'], $instruments['vmts_1'], 'draft', 'Visi keilmuan Sistem Informasi sudah ditetapkan dan diarahkan pada transformasi digital organisasi.', 'SK dan dokumen kurikulum tersedia.', null, 'Belum semua bukti sosialisasi diunggah.', 'Mengunggah berita acara sosialisasi.'],
            'si_education_1' => [$assignments['si'], $instruments['education_1'], 'belum_diisi', null, null, null, null, null],
        ];

        $assessments = [];

        foreach ($records as $key => [$assignment, $instrument, $status, $answer, $realization, $obstacle, $gap, $plan]) {
            $assessment = SelfAssessment::query()->create([
                'assignment_id' => $assignment->id,
                'instrument_id' => $instrument->id,
                'jawaban_naratif' => $answer,
                'realisasi' => $realization,
                'target' => $instrument->target_kriteria,
                'kendala' => $obstacle,
                'analisis_gap' => $gap,
                'rencana_perbaikan_awal' => $plan,
                'status' => $status,
            ]);

            $assessments[$key] = $assessment;
        }

        Evidence::query()->create([
            'self_assessment_id' => $assessments['ti_vmts_1']->id,
            'nama_dokumen' => 'SK Penetapan Visi Keilmuan Prodi TI 2024',
            'jenis_dokumen' => 'SK Dekan',
            'tipe_sumber' => 'tautan',
            'url_tautan' => 'https://repository.jdsuniversity.ac.id/ami/ti/sk-visi-keilmuan-2024.pdf',
            'tahun_dokumen' => 2024,
            'deskripsi' => 'SK penetapan visi keilmuan dan arah pengembangan Prodi TI.',
            'uploaded_by' => $users['auditee_ti']->id,
            'instrumen_terkait' => $instruments['vmts_1']->kode,
            'instrument_ids' => [$instruments['vmts_1']->id],
            'status_verifikasi' => 'valid',
        ]);

        Evidence::query()->create([
            'self_assessment_id' => $assessments['ti_gov_1']->id,
            'nama_dokumen' => 'Draft Uraian Tugas Koordinator Laboratorium',
            'jenis_dokumen' => 'Draft SOP',
            'tipe_sumber' => 'tautan',
            'url_tautan' => 'https://repository.jdsuniversity.ac.id/ami/ti/draft-uraian-tugas-lab.docx',
            'tahun_dokumen' => 2026,
            'deskripsi' => 'Draft uraian tugas yang sedang menunggu pengesahan.',
            'uploaded_by' => $users['auditee_ti']->id,
            'instrumen_terkait' => $instruments['gov_1']->kode,
            'instrument_ids' => [$instruments['gov_1']->id],
            'status_verifikasi' => 'perlu_klarifikasi',
        ]);

        return $assessments;
    }

    /**
     * @param  array<string, AuditAssignment>  $assignments
     * @param  array<string, Instrument>  $instruments
     * @param  array<string, SelfAssessment>  $assessments
     * @param  array<string, User>  $users
     */
    private function seedEvaluations(array $assignments, array $instruments, array $assessments, array $users): void
    {
        foreach ([
            ['ti_vmts_1', 'valid', 'Dokumen valid dan konsisten dengan kurikulum berjalan.', false, null, 'final', 3.50],
            ['ti_vmts_2', 'valid', 'Sosialisasi cukup baik, perlu bukti tambahan untuk alumni.', false, null, 'final', 3.25],
            ['ti_gov_1', 'perlu_klarifikasi', 'Mohon unggah SK final atau bukti proses pengesahan uraian tugas laboratorium.', true, 'Pastikan uraian tugas koordinator laboratorium disahkan sebelum visitasi.', 'menunggu_klarifikasi', 2.00],
            ['ti_gov_2', 'valid', 'PPEPP berjalan, tetapi matriks tindak lanjut perlu dibuat lebih terukur.', true, 'Buat daftar tindak lanjut dengan PIC, target, dan status penyelesaian.', 'berlangsung', 2.75],
        ] as [$assessmentKey, $evidenceStatus, $note, $finding, $recommendation, $status, $score]) {
            $assessment = $assessments[$assessmentKey];

            Evaluation::query()->create([
                'assignment_id' => $assessment->assignment_id,
                'instrument_id' => $assessment->instrument_id,
                'self_assessment_id' => $assessment->id,
                'skor' => $score,
                'status_bukti' => $evidenceStatus,
                'catatan_auditor' => $note,
                'usulan_temuan' => $finding,
                'rekomendasi_awal' => $recommendation,
                'status_pemeriksaan' => $status,
                'diperiksa_oleh' => $users['auditor_lead']->id,
            ]);
        }
    }

    /**
     * @param  array<string, AuditAssignment>  $assignments
     * @param  array<string, Instrument>  $instruments
     * @param  array<string, User>  $users
     */
    private function seedClarifications(array $assignments, array $instruments, array $users): void
    {
        $clarification = Clarification::query()->create([
            'assignment_id' => $assignments['ti']->id,
            'instrument_id' => $instruments['gov_1']->id,
            'dibuka_oleh' => $users['auditor_lead']->id,
            'status' => 'dijawab',
        ]);

        ClarificationMessage::query()->create([
            'clarification_id' => $clarification->id,
            'pengirim_id' => $users['auditor_lead']->id,
            'isi_pesan' => 'Mohon konfirmasi apakah uraian tugas koordinator laboratorium sudah disahkan atau masih berbentuk draft.',
            'created_at' => now()->subDays(2),
        ]);

        ClarificationMessage::query()->create([
            'clarification_id' => $clarification->id,
            'pengirim_id' => $users['auditee_ti']->id,
            'isi_pesan' => 'Dokumen masih draft dan dijadwalkan disahkan pada rapat fakultas tanggal 18 Juli 2026. Bukti undangan rapat sudah kami siapkan.',
            'created_at' => now()->subDay(),
        ]);
    }

    /**
     * @param  array<string, AuditAssignment>  $assignments
     * @param  array<string, User>  $users
     */
    private function seedVisits(array $assignments, array $users): void
    {
        $visit = Visit::query()->create([
            'assignment_id' => $assignments['ti']->id,
            'tanggal' => '2026-08-05',
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:30',
            'tipe' => 'lapangan',
            'lokasi_atau_tautan' => 'Ruang Rapat Fakultas Teknologi Informasi Lt. 2',
            'agenda' => 'Pembukaan, konfirmasi dokumen PPEPP, wawancara kaprodi, verifikasi laboratorium, dan penutupan.',
            'catatan_wawancara' => 'Kaprodi menjelaskan perbaikan dokumen PPEPP dan rencana pengesahan uraian tugas laboratorium.',
            'catatan_observasi' => 'Laboratorium pembelajaran aktif digunakan, namun daftar pemeliharaan perangkat belum dipublikasikan secara rutin.',
            'kesimpulan' => 'Visitasi dapat dilanjutkan dengan fokus pada validasi pengesahan dokumen tata kelola.',
            'status' => 'selesai',
            'konfirmasi_auditee' => true,
            'waktu_konfirmasi_auditee' => now()->subDays(3),
        ]);

        foreach ([
            ['Dr. Rafi Nugroho', 'Lead Auditor', 'auditor'],
            ['Dr. Annisa Kartika', 'Auditor Anggota', 'auditor'],
            ['Dr. Rina Puspitasari', 'Ketua Program Studi', 'auditee'],
            ['Ir. Bayu Prakoso', 'Koordinator Laboratorium', 'auditee'],
        ] as [$name, $position, $type]) {
            DB::table('visit_participants')->insert([
                'visit_id' => $visit->id,
                'nama_peserta' => $name,
                'jabatan' => $position,
                'tipe' => $type,
                'created_at' => now(),
            ]);
        }

        DB::table('visit_attachments')->insert([
            'visit_id' => $visit->id,
            'nama_file' => 'Dokumentasi visitasi laboratorium TI',
            'tipe_sumber' => 'tautan',
            'url_tautan' => 'https://repository.jdsuniversity.ac.id/ami/ti/dokumentasi-visitasi-lab',
            'diunggah_oleh' => $users['auditee_ti']->id,
            'keterangan' => 'Dokumentasi foto dan daftar hadir visitasi.',
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, AuditAssignment>  $assignments
     * @param  array<string, Standard>  $standards
     * @param  array<string, Instrument>  $instruments
     * @param  array<string, User>  $users
     */
    private function seedFindingsAndFollowUps(array $assignments, array $standards, array $instruments, array $users): void
    {
        $finding = Finding::query()->create([
            'nomor_temuan' => 'AMI2026-TI-001',
            'assignment_id' => $assignments['ti']->id,
            'standard_id' => $standards['governance']->id,
            'instrument_id' => $instruments['gov_2']->id,
            'kategori' => 'minor',
            'prioritas' => 'sedang',
            'kondisi_aktual' => 'Matriks tindak lanjut PPEPP belum mencantumkan PIC, target waktu, dan status penyelesaian secara konsisten.',
            'kriteria' => 'Program studi menjalankan siklus PPEPP secara berkala dan terdokumentasi sampai tahap peningkatan.',
            'bukti_objektif' => 'Laporan monev pembelajaran tersedia, namun tabel tindak lanjut hanya berisi daftar isu tanpa PIC dan target waktu.',
            'akar_masalah_awal' => 'Belum ada format baku tindak lanjut PPEPP di tingkat program studi.',
            'rekomendasi_auditor' => 'Susun matriks tindak lanjut PPEPP dengan kolom isu, akar masalah, PIC, target penyelesaian, status, dan bukti penyelesaian.',
            'target_penyelesaian' => '2026-08-30',
            'status' => 'menunggu_verifikasi',
            'dibuat_oleh' => $users['auditor_lead']->id,
            'difinalisasi_oleh' => $users['auditor_lead']->id,
            'waktu_finalisasi' => now()->subDays(4),
        ]);

        DB::table('finding_status_histories')->insert([
            'finding_id' => $finding->id,
            'dari_status' => 'draft',
            'ke_status' => 'aktif',
            'catatan' => 'Temuan difinalisasi dan dikirim ke auditee.',
            'changed_by' => $users['auditor_lead']->id,
            'created_at' => now()->subDays(4),
        ]);

        $followUp = FollowUp::query()->create([
            'finding_id' => $finding->id,
            'assignment_id' => $assignments['ti']->id,
            'rencana_tindakan' => 'Menyusun format matriks tindak lanjut PPEPP dan membahasnya pada rapat prodi.',
            'penanggung_jawab' => 'Sekretaris Prodi dan Gugus Kendali Mutu Prodi',
            'target_penyelesaian' => '2026-08-25',
            'indikator_keberhasilan' => 'Matriks tindak lanjut PPEPP tersedia, disahkan kaprodi, dan minimal 80% isu memiliki PIC serta target waktu.',
            'progres' => 'berlangsung',
            'kendala' => null,
            'catatan_auditee' => 'Format matriks sudah disusun dan menunggu persetujuan pada rapat prodi.',
            'status' => 'diajukan',
            'dibuat_oleh' => $users['auditee_ti']->id,
        ]);

        Evidence::query()->create([
            'follow_up_id' => $followUp->id,
            'nama_dokumen' => 'Draft Matriks Tindak Lanjut PPEPP Prodi TI',
            'jenis_dokumen' => 'Matriks Tindak Lanjut',
            'tipe_sumber' => 'tautan',
            'url_tautan' => 'https://repository.jdsuniversity.ac.id/ami/ti/draft-matriks-tindak-lanjut-ppepp.xlsx',
            'tahun_dokumen' => 2026,
            'deskripsi' => 'Draft matriks tindak lanjut PPEPP untuk verifikasi auditor.',
            'uploaded_by' => $users['auditee_ti']->id,
            'instrumen_terkait' => $instruments['gov_2']->kode,
            'status_verifikasi' => 'belum_diperiksa',
        ]);

        $closedFinding = Finding::query()->create([
            'nomor_temuan' => 'AMI2026-TI-002',
            'assignment_id' => $assignments['ti']->id,
            'standard_id' => $standards['vmts']->id,
            'instrument_id' => $instruments['vmts_2']->id,
            'kategori' => 'peluang_peningkatan',
            'prioritas' => 'rendah',
            'kondisi_aktual' => 'Dokumentasi sosialisasi VMTS kepada alumni belum tersimpan di repositori mutu.',
            'kriteria' => 'VMTS disosialisasikan kepada seluruh pemangku kepentingan dan terdokumentasi.',
            'bukti_objektif' => 'Website prodi tersedia, namun dokumentasi forum alumni belum terunggah.',
            'akar_masalah_awal' => 'Dokumentasi forum alumni masih tersimpan di panitia kegiatan.',
            'rekomendasi_auditor' => 'Unggah berita acara forum alumni dan materi sosialisasi VMTS ke repositori mutu.',
            'target_penyelesaian' => '2026-07-28',
            'status' => 'ditutup',
            'dibuat_oleh' => $users['auditor_lead']->id,
            'difinalisasi_oleh' => $users['auditor_lead']->id,
            'waktu_finalisasi' => now()->subDays(8),
        ]);

        $closedFollowUp = FollowUp::query()->create([
            'finding_id' => $closedFinding->id,
            'assignment_id' => $assignments['ti']->id,
            'rencana_tindakan' => 'Mengunggah berita acara forum alumni dan materi sosialisasi VMTS.',
            'penanggung_jawab' => 'Koordinator Kemahasiswaan dan Alumni',
            'target_penyelesaian' => '2026-07-25',
            'indikator_keberhasilan' => 'Dokumen tersedia di repositori mutu dan dapat diakses auditor.',
            'progres' => 'selesai',
            'catatan_auditee' => 'Dokumen sudah diunggah dan tautan repositori telah dibagikan.',
            'status' => 'disetujui',
            'dibuat_oleh' => $users['auditee_ti']->id,
        ]);

        FollowUpVerification::query()->create([
            'follow_up_id' => $closedFollowUp->id,
            'verifikator_id' => $users['auditor_lead']->id,
            'keputusan' => 'disetujui',
            'catatan_verifikasi' => 'Bukti sudah sesuai dan dapat diakses.',
            'waktu_verifikasi' => now()->subDays(2),
        ]);
    }

    /**
     * @param  array<string, AuditAssignment>  $assignments
     * @param  array<string, User>  $users
     */
    private function seedNotifications(array $assignments, array $users): void
    {
        foreach ([
            [$users['auditee_ti'], 'visitasi_dijadwalkan', 'Jadwal Visitasi TI', 'Visitasi Program Studi Teknik Informatika dijadwalkan pada 5 Agustus 2026.', '/auditee/jadwal-visitasi', 'audit_assignment', $assignments['ti']->id],
            [$users['auditee_ti'], 'klarifikasi_dibuat', 'Klarifikasi Auditor', 'Auditor meminta klarifikasi uraian tugas laboratorium Prodi TI.', '/auditee/klarifikasi-auditor', 'audit_assignment', $assignments['ti']->id],
            [$users['auditor_lead'], 'tindak_lanjut_diajukan', 'Tindak Lanjut Menunggu Verifikasi', 'Auditee TI mengajukan tindak lanjut temuan AMI2026-TI-001.', '/auditor/verifikasi-tindak-lanjut', 'audit_assignment', $assignments['ti']->id],
            [$users['auditee_si'], 'penugasan_dibuat', 'Penugasan Audit SI', 'Program Studi Sistem Informasi masuk penugasan AMI Semester Genap 2025/2026.', '/auditee/dashboard', 'audit_assignment', $assignments['si']->id],
        ] as [$user, $type, $title, $message, $url, $objectType, $objectId]) {
            Notification::query()->create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'tipe' => $type,
                'judul' => $title,
                'isi' => $message,
                'url_tujuan' => $url,
                'objek_tipe' => $objectType,
                'objek_id' => $objectId,
                'is_read' => false,
                'type' => 'siami',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => [
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                    'object_type' => $objectType,
                    'object_id' => $objectId,
                ],
            ]);
        }
    }
}

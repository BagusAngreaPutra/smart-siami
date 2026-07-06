<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\AuditPeriod;
use App\Models\FindingCategory;
use App\Models\Instrument;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Models\Standard;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $unit = Unit::query()->updateOrCreate(
            ['kode' => 'SI'],
            [
                'nama' => 'Program Studi Sistem Informasi',
                'jenis_unit' => 'prodi',
                'fakultas_induk' => 'Fakultas Teknologi Informasi',
                'nama_pimpinan' => 'Dr. Sinta Rahma',
                'email' => 'si@siami.test',
                'phone' => '080000001001',
                'is_active' => true,
            ],
        );

        $facultyUnit = Unit::query()->updateOrCreate(
            ['kode' => 'FTI'],
            [
                'nama' => 'Fakultas Teknologi Informasi',
                'jenis_unit' => 'fakultas',
                'fakultas_induk' => null,
                'nama_pimpinan' => 'Prof. Budi Santoso',
                'email' => 'fti@siami.test',
                'phone' => '080000001002',
                'is_active' => true,
            ],
        );

        $qualityUnit = Unit::query()->updateOrCreate(
            ['kode' => 'LPM'],
            [
                'nama' => 'Lembaga Penjaminan Mutu',
                'jenis_unit' => 'unit_kerja',
                'fakultas_induk' => null,
                'nama_pimpinan' => 'Ir. Dewi Lestari',
                'email' => 'lpm@siami.test',
                'phone' => '080000001003',
                'is_active' => true,
            ],
        );

        $admin = User::query()->updateOrCreate(
            ['nip_nidn' => '0000000001'],
            [
                'name' => 'Admin SIAMI',
                'email' => 'cedokajaib12@gmail.com',
                'phone' => '080000000001',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'unit_id' => null,
                'is_active' => true,
            ],
        );

        $auditor = User::query()->updateOrCreate(
            ['nip_nidn' => '0000000002'],
            [
                'name' => 'Auditor SIAMI',
                'email' => 'angreaputrabagus@gmail.com',
                'phone' => '080000000002',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditor,
                'unit_id' => null,
                'is_active' => true,
            ],
        );

        $auditorTwo = User::query()->updateOrCreate(
            ['nip_nidn' => '0000000004'],
            [
                'name' => 'Auditor Pendamping SIAMI',
                'email' => 'auditor2@siami.test',
                'phone' => '080000000004',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditor,
                'unit_id' => null,
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['nip_nidn' => '0000000003'],
            [
                'name' => 'Kaprodi Sistem Informasi',
                'email' => 'anggreaputrabagus@gmail.com',
                'phone' => '080000000003',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditee,
                'unit_id' => $unit->id,
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'auditee2@siami.test'],
            [
                'name' => 'Dekan Fakultas Teknologi Informasi',
                'nip_nidn' => '0000000005',
                'phone' => '080000000005',
                'password' => Hash::make('password'),
                'role' => UserRole::Auditee,
                'unit_id' => $facultyUnit->id,
                'is_active' => true,
            ],
        );

        $standardOne = Standard::query()->updateOrCreate(
            ['kode' => 'S1'],
            [
                'nama' => 'Visi, Misi, Tujuan, dan Strategi',
                'deskripsi' => 'Standar mutu terkait arah pengembangan unit dan keterkaitannya dengan institusi.',
                'target' => 'VMTS tersedia, dipahami, dan digunakan sebagai acuan penyelenggaraan program.',
                'is_active' => true,
                'urutan' => 1,
            ],
        );

        $standardTwo = Standard::query()->updateOrCreate(
            ['kode' => 'S2'],
            [
                'nama' => 'Tata Pamong, Tata Kelola, dan Kerja Sama',
                'deskripsi' => 'Standar mutu terkait pengelolaan unit, kepemimpinan, penjaminan mutu, dan kerja sama.',
                'target' => 'Tata kelola berjalan efektif, terdokumentasi, dan dievaluasi berkala.',
                'is_active' => true,
                'urutan' => 2,
            ],
        );

        $instruments = [
            [
                'standard_id' => $standardOne->id,
                'kode' => 'S1-01',
                'nama_indikator' => 'Ketersediaan dokumen VMTS',
                'pertanyaan' => 'Apakah unit memiliki dokumen visi, misi, tujuan, dan strategi yang sah dan mutakhir?',
                'jenis_jawaban' => 'narasi',
                'target_kriteria' => 'Dokumen VMTS tersedia, disahkan, dan selaras dengan VMTS institusi.',
                'bobot' => 10,
                'panduan_pengisian' => 'Jelaskan dokumen yang tersedia dan tahun pengesahannya.',
                'bukti_diperlukan' => 'Dokumen VMTS, SK penetapan, dokumen sosialisasi.',
                'urutan' => 1,
            ],
            [
                'standard_id' => $standardOne->id,
                'kode' => 'S1-02',
                'nama_indikator' => 'Pemahaman VMTS',
                'pertanyaan' => 'Berapa tingkat pemahaman sivitas akademika terhadap VMTS unit?',
                'jenis_jawaban' => 'skor',
                'target_kriteria' => 'Skor tinggi menunjukkan pemahaman VMTS yang merata.',
                'bobot' => 10,
                'panduan_pengisian' => 'Gunakan hasil survei terakhir sebagai dasar skor.',
                'bukti_diperlukan' => 'Rekap survei pemahaman VMTS.',
                'skor_min' => 1,
                'skor_max' => 4,
                'urutan' => 2,
            ],
            [
                'standard_id' => $standardOne->id,
                'kode' => 'S1-03',
                'nama_indikator' => 'Sosialisasi VMTS',
                'pertanyaan' => 'Media apa saja yang digunakan unit untuk sosialisasi VMTS?',
                'jenis_jawaban' => 'pilihan',
                'target_kriteria' => 'Sosialisasi dilakukan melalui beberapa kanal dan terdokumentasi.',
                'bobot' => 8,
                'panduan_pengisian' => 'Pilih opsi yang paling menggambarkan kondisi unit.',
                'bukti_diperlukan' => 'Dokumentasi sosialisasi, laman web, materi kegiatan.',
                'opsi_jawaban' => ['Belum ada', 'Satu kanal', 'Beberapa kanal', 'Beberapa kanal dan dievaluasi'],
                'urutan' => 3,
            ],
            [
                'standard_id' => $standardTwo->id,
                'kode' => 'S2-01',
                'nama_indikator' => 'Struktur organisasi',
                'pertanyaan' => 'Unggah bukti struktur organisasi dan uraian tugas unit yang berlaku.',
                'jenis_jawaban' => 'unggah_file',
                'target_kriteria' => 'Struktur organisasi dan uraian tugas tersedia, sah, dan dipahami.',
                'bobot' => 10,
                'panduan_pengisian' => 'Unggah dokumen resmi dalam format PDF.',
                'bukti_diperlukan' => 'Bagan struktur organisasi, uraian tugas, SK pengangkatan.',
                'urutan' => 1,
            ],
            [
                'standard_id' => $standardTwo->id,
                'kode' => 'S2-02',
                'nama_indikator' => 'Evaluasi tata kelola',
                'pertanyaan' => 'Jelaskan pelaksanaan evaluasi tata kelola unit dan unggah bukti pendukungnya.',
                'jenis_jawaban' => 'kombinasi',
                'target_kriteria' => 'Evaluasi tata kelola dilakukan berkala dengan tindak lanjut terdokumentasi.',
                'bobot' => 12,
                'panduan_pengisian' => 'Isi narasi singkat dan sertakan dokumen bukti evaluasi.',
                'bukti_diperlukan' => 'Notulen rapat evaluasi, laporan tinjauan manajemen, rencana tindak lanjut.',
                'kombinasi_jawaban' => ['narasi', 'unggah_file'],
                'urutan' => 2,
            ],
        ];

        foreach ($instruments as $instrument) {
            Instrument::query()->updateOrCreate(
                ['standard_id' => $instrument['standard_id'], 'kode' => $instrument['kode']],
                ['is_active' => true, ...$instrument],
            );
        }

        $activePeriod = AuditPeriod::query()->updateOrCreate(
            ['nama' => 'AMI Semester Genap 2025/2026'],
            [
                'tahun_akademik' => '2025/2026',
                'jenis_audit' => 'akademik',
                'tanggal_mulai' => '2026-06-01',
                'batas_evaluasi_diri' => '2026-07-15',
                'batas_desk_evaluation' => '2026-07-20',
                'visitasi_mulai' => '2026-07-25',
                'visitasi_selesai' => '2026-07-30',
                'batas_tindak_lanjut' => '2026-08-15',
                'status' => 'aktif',
                'catatan' => 'Periode contoh untuk pengujian alur AMI.',
                'created_by' => $admin->id,
            ],
        );

        $assignmentOne = AuditAssignment::query()->updateOrCreate(
            ['audit_period_id' => $activePeriod->id, 'unit_id' => $unit->id, 'status' => 'aktif'],
            [
                'lead_auditor_id' => $auditor->id,
                'tanggal_desk_evaluation' => '2026-06-20',
                'jadwal_visitasi' => '2026-07-02',
                'catatan_penugasan' => 'Fokus audit pada kesiapan dokumen evaluasi diri program studi.',
            ],
        );
        $assignmentOne->auditors()->sync([
            $auditor->id => ['peran_dalam_tim' => 'lead'],
            $auditorTwo->id => ['peran_dalam_tim' => 'anggota'],
        ]);

        $assignmentTwo = AuditAssignment::query()->updateOrCreate(
            ['audit_period_id' => $activePeriod->id, 'unit_id' => $facultyUnit->id, 'status' => 'aktif'],
            [
                'lead_auditor_id' => $auditorTwo->id,
                'tanggal_desk_evaluation' => '2026-06-22',
                'jadwal_visitasi' => '2026-07-04',
                'catatan_penugasan' => 'Audit tata kelola fakultas dan dukungan mutu akademik.',
            ],
        );
        $assignmentTwo->auditors()->sync([
            $auditorTwo->id => ['peran_dalam_tim' => 'lead'],
            $auditor->id => ['peran_dalam_tim' => 'anggota'],
        ]);

        foreach ([
            'nama_institusi' => 'Universitas Contoh SIAMI',
            'logo_path' => null,
            'nama_lpm' => 'Lembaga Penjaminan Mutu',
            'email_lpm' => 'lpm@siami.test',
            'max_file_size_mb' => '10',
            'allowed_file_types' => 'pdf,docx,xlsx,jpg,png',
            'report_paper_size' => 'A4',
            'report_orientation' => 'portrait',
            'report_margin_top_cm' => '1.8',
            'report_margin_right_cm' => '1.6',
            'report_margin_bottom_cm' => '1.8',
            'report_margin_left_cm' => '1.6',
            'report_font_family' => 'Arial',
            'report_font_size' => '12',
            'report_line_height' => '1.45',
            'report_table_density' => 'normal',
            'report_show_visual_summary' => '1',
            'report_letterhead_mode' => 'default',
            'report_letterhead_institution' => 'Universitas JDS',
            'report_letterhead_unit' => 'Lembaga Penjaminan Mutu',
            'report_letterhead_address' => 'Jl. Contoh Kampus JDS No. 10, Kota Pendidikan 12345',
            'report_letterhead_contact' => 'Telp. (021) 555-0199 | Email: lpm@universitasjds.test | www.universitasjds.test',
            'report_letterhead_file_path' => null,
            'report_letterhead_file_name' => null,
            'report_letterhead_file_type' => null,
            'report_letterhead_logo_width' => '88',
            'report_letterhead_institution_font_size' => '16',
            'report_letterhead_unit_font_size' => '14',
            'report_letterhead_address_font_size' => '11',
            'report_letterhead_institution_bold' => '1',
            'report_letterhead_unit_bold' => '1',
            'report_letterhead_address_bold' => '0',
            'email_notifications_enabled' => '1',
        ] as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        foreach ([
            ['nama' => 'Observasi', 'warna_hex' => '#667085', 'urutan' => 1],
            ['nama' => 'Peluang Peningkatan', 'warna_hex' => '#176b87', 'urutan' => 2],
            ['nama' => 'Minor', 'warna_hex' => '#c2410c', 'urutan' => 3],
            ['nama' => 'Mayor', 'warna_hex' => '#b42318', 'urutan' => 4],
        ] as $category) {
            FindingCategory::query()->updateOrCreate(['nama' => $category['nama']], [
                ...$category,
                'is_active' => true,
            ]);
        }

        foreach ([
            'periode_dibuka' => ['Periode Audit Dibuka', 'Periode audit {nama_periode} telah dibuka.'],
            'penugasan_dibuat' => ['Penugasan Audit', 'Anda ditugaskan pada unit {nama_unit} untuk periode {nama_periode}.'],
            'evaluasi_diri_dikirim' => ['Evaluasi Diri Dikirim', 'Evaluasi diri unit {nama_unit} telah difinalisasi.'],
            'desk_evaluation_diperbarui' => ['Desk Evaluation Diperbarui', 'Auditor telah memperbarui desk evaluation untuk unit {nama_unit}.'],
            'klarifikasi_dibuat' => ['Klarifikasi Auditor', 'Auditor meminta klarifikasi untuk unit {nama_unit}.'],
            'klarifikasi_dijawab' => ['Klarifikasi Dijawab', 'Auditee telah menjawab klarifikasi.'],
            'klarifikasi_pesan_baru' => ['Pesan Baru dari Auditor', 'Auditor mengirim pesan baru pada klarifikasi unit {nama_unit}.'],
            'klarifikasi_lampiran_baru' => ['Lampiran Klarifikasi Baru', 'Auditor menambahkan lampiran pada klarifikasi unit {nama_unit}.'],
            'klarifikasi_selesai' => ['Klarifikasi Selesai', 'Auditor menandai klarifikasi unit {nama_unit} selesai.'],
            'klarifikasi_dibuka_kembali' => ['Klarifikasi Dibuka Kembali', 'Auditor membuka kembali klarifikasi unit {nama_unit}.'],
            'visitasi_dijadwalkan' => ['Jadwal Visitasi', 'Visitasi unit {nama_unit} telah dijadwalkan.'],
            'berita_acara_dikirim' => ['Berita Acara Visitasi', 'Berita acara visitasi unit {nama_unit} telah dikirim.'],
            'temuan_difinalisasi' => ['Temuan Audit Baru', 'Temuan {nomor_temuan} telah difinalisasi.'],
            'tindak_lanjut_diajukan' => ['Tindak Lanjut Diajukan', 'Tindak lanjut temuan {nomor_temuan} menunggu verifikasi.'],
            'tindak_lanjut_diverifikasi' => ['Tindak Lanjut Diverifikasi', 'Keputusan verifikasi tindak lanjut temuan {nomor_temuan} telah tersedia.'],
            'pengingat_batas_waktu' => ['Pengingat Batas Waktu', 'Batas waktu {batas_waktu} untuk unit {nama_unit} sudah dekat.'],
            'pengingat_manual' => ['Pengingat dari Admin', 'Mohon segera melengkapi proses audit unit {nama_unit}.'],
        ] as $type => [$title, $body]) {
            NotificationTemplate::query()->updateOrCreate(['tipe' => $type], [
                'judul_template' => $title,
                'isi_template' => $body,
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GuideController extends Controller
{
    public function auditor(): View
    {
        return view('guides.show', [
            'roleLabel' => 'Auditor',
            'eyebrow' => 'Panduan Auditor',
            'title' => 'Alur Kerja Auditor di SMART SIAMI',
            'description' => 'Gunakan halaman ini sebagai peta kerja: mulai dari melihat tugas, memeriksa evaluasi diri, meminta klarifikasi, mencatat visitasi, membuat temuan, sampai memverifikasi tindak lanjut.',
            'illustration' => 'images/guides/auditor-guide-hero.png',
            'illustrationWebp' => 'images/guides/auditor-guide-hero.webp',
            'illustrationAlt' => 'Ilustrasi Auditor SIAMI memeriksa bukti dan progres audit dalam ruang kerja digital kampus.',
            'dashboardRoute' => route('auditor.dashboard'),
            'workflow' => [
                'Terima penugasan',
                'Periksa evaluasi diri',
                'Minta klarifikasi bila perlu',
                'Lakukan visitasi',
                'Finalisasi temuan',
                'Verifikasi tindak lanjut',
                'Unduh laporan',
            ],
            'sections' => [
                [
                    'title' => 'Dashboard Auditor',
                    'url' => route('auditor.dashboard'),
                    'purpose' => 'Melihat ringkasan pekerjaan yang paling mendesak.',
                    'when' => 'Dibuka pertama kali setelah login untuk menentukan prioritas hari ini.',
                    'steps' => [
                        'Cek kartu Tugas Aktif untuk melihat jumlah unit yang menjadi tanggung jawab Anda.',
                        'Lihat Instrumen Belum Diperiksa untuk mengetahui sisa butir yang perlu dinilai.',
                        'Perhatikan banner deadline jika batas desk evaluation sudah dekat.',
                        'Klik angka atau daftar unit untuk masuk ke data terkait.',
                    ],
                    'result' => 'Anda tahu unit mana yang harus diperiksa lebih dulu.',
                ],
                [
                    'title' => 'Tugas Audit',
                    'url' => route('auditor.tasks'),
                    'purpose' => 'Melihat daftar penugasan audit yang melibatkan Anda.',
                    'when' => 'Dipakai saat ingin mengecek unit, periode, jadwal, atau peran Anda dalam tim audit.',
                    'steps' => [
                        'Buka menu Tugas Audit.',
                        'Pilih penugasan berdasarkan unit atau periode.',
                        'Cek informasi lead auditor, anggota tim, dan jadwal yang sudah ditetapkan.',
                    ],
                    'result' => 'Anda memahami lingkup penugasan sebelum mulai memeriksa.',
                ],
                [
                    'title' => 'Desk Evaluation',
                    'url' => route('auditor.desk-evaluation'),
                    'purpose' => 'Memeriksa jawaban auditee dan bukti dokumen per instrumen.',
                    'when' => 'Dilakukan setelah auditee mengirim evaluasi diri.',
                    'steps' => [
                        'Pilih unit dari daftar tugas desk evaluation.',
                        'Baca pertanyaan instrumen, jawaban auditee, realisasi, kendala, dan analisis gap.',
                        'Buka atau unduh bukti pendukung yang dilampirkan.',
                        'Isi status bukti, skor bila instrumen memerlukan skor, catatan auditor, dan rekomendasi awal.',
                        'Simpan per instrumen agar catatan tidak hilang.',
                        'Finalisasi desk evaluation setelah semua instrumen sudah diperiksa.',
                    ],
                    'result' => 'Catatan pemeriksaan terkunci sebagai dasar klarifikasi, visitasi, dan temuan.',
                ],
                [
                    'title' => 'Klarifikasi',
                    'url' => route('auditor.clarifications'),
                    'purpose' => 'Meminta penjelasan tambahan dari auditee secara terdokumentasi.',
                    'when' => 'Dipakai jika jawaban atau bukti belum jelas, kurang lengkap, atau perlu konfirmasi.',
                    'steps' => [
                        'Dari Desk Evaluation, klik Kirim Klarifikasi pada instrumen terkait.',
                        'Tulis pertanyaan dengan jelas: apa yang kurang, bukti apa yang dibutuhkan, dan batas konteksnya.',
                        'Pantau status klarifikasi: terbuka, dijawab, selesai, atau dibuka kembali.',
                        'Tandai selesai jika jawaban auditee sudah cukup.',
                    ],
                    'result' => 'Riwayat komunikasi tersimpan dan terhubung ke instrumen audit.',
                ],
                [
                    'title' => 'Visitasi',
                    'url' => route('auditor.visitations'),
                    'purpose' => 'Mengatur dan mencatat proses verifikasi lapangan atau daring.',
                    'when' => 'Dipakai setelah desk evaluation untuk memvalidasi bukti, wawancara, dan observasi.',
                    'steps' => [
                        'Pilih unit visitasi.',
                        'Isi tanggal, waktu, tipe visitasi, lokasi atau tautan, dan agenda.',
                        'Tambahkan peserta dari auditor, auditee, atau pihak lain.',
                        'Catat wawancara, observasi, lampiran pendukung, dan kesimpulan.',
                        'Tandai selesai lalu buat atau kirim berita acara ke auditee.',
                    ],
                    'result' => 'Berita acara visitasi siap menjadi bukti proses audit.',
                ],
                [
                    'title' => 'Temuan',
                    'url' => route('auditor.findings'),
                    'purpose' => 'Mencatat hasil audit resmi yang perlu ditindaklanjuti unit.',
                    'when' => 'Dipakai jika ada ketidaksesuaian, observasi, atau peluang peningkatan.',
                    'steps' => [
                        'Klik Tambah Temuan.',
                        'Pilih assignment, standar, dan instrumen terkait.',
                        'Isi kategori, prioritas, kondisi aktual, kriteria, bukti objektif, akar masalah awal, rekomendasi, dan target penyelesaian.',
                        'Simpan sebagai draft jika belum yakin.',
                        'Finalisasi dan kirim ke auditee jika isi temuan sudah siap.',
                    ],
                    'result' => 'Temuan aktif diterima auditee dan masuk ke proses tindak lanjut.',
                ],
                [
                    'title' => 'Verifikasi Perbaikan',
                    'url' => route('auditor.follow-up-verifications'),
                    'purpose' => 'Menilai apakah perbaikan auditee sudah cukup menutup temuan.',
                    'when' => 'Dipakai setelah auditee mengajukan tindak lanjut beserta bukti.',
                    'steps' => [
                        'Buka daftar tindak lanjut menunggu verifikasi.',
                        'Baca rencana tindakan, PIC, target, indikator keberhasilan, dan bukti.',
                        'Pilih keputusan: disetujui atau perlu perbaikan.',
                        'Berikan catatan auditor jika masih perlu revisi.',
                    ],
                    'result' => 'Temuan dapat ditutup atau dikembalikan ke auditee untuk perbaikan.',
                ],
                [
                    'title' => 'Laporan Saya',
                    'url' => route('auditor.reports'),
                    'purpose' => 'Mengunduh laporan dari penugasan yang menjadi tanggung jawab Anda.',
                    'when' => 'Dipakai saat butuh dokumen rekap evaluasi, desk evaluation, visitasi, temuan, tindak lanjut, atau laporan hasil audit unit.',
                    'steps' => [
                        'Pilih periode dan unit yang relevan.',
                        'Pilih jenis laporan.',
                        'Gunakan Pratinjau untuk membaca di browser.',
                        'Gunakan Unduh untuk menyimpan PDF atau Excel.',
                    ],
                    'result' => 'Dokumen audit siap digunakan untuk arsip atau rapat mutu.',
                ],
            ],
            'notes' => [
                'Auditor hanya dapat membuka data unit yang masuk dalam penugasannya.',
                'Jawaban auditee dan bukti bersifat read-only bagi auditor.',
                'Finalisasi sebaiknya dilakukan setelah semua instrumen sudah diperiksa.',
            ],
        ]);
    }

    public function auditee(): View
    {
        return view('guides.show', [
            'roleLabel' => 'Auditee',
            'eyebrow' => 'Panduan Auditee',
            'title' => 'Alur Kerja Auditee di SMART SIAMI',
            'description' => 'Panduan ini membantu unit mengisi evaluasi diri, melengkapi bukti, menjawab klarifikasi, mengonfirmasi visitasi, dan menyelesaikan tindak lanjut temuan.',
            'illustration' => 'images/guides/auditee-guide-hero.png',
            'illustrationWebp' => 'images/guides/auditee-guide-hero.webp',
            'illustrationAlt' => 'Ilustrasi tim Auditee SIAMI menyusun evaluasi diri, bukti dokumen, dan jadwal audit unit.',
            'dashboardRoute' => route('auditee.dashboard'),
            'workflow' => [
                'Baca tugas unit',
                'Isi evaluasi diri',
                'Unggah bukti',
                'Jawab klarifikasi',
                'Konfirmasi visitasi',
                'Tindak lanjuti temuan',
                'Unduh laporan',
            ],
            'sections' => [
                [
                    'title' => 'Dashboard Auditee',
                    'url' => route('auditee.dashboard'),
                    'purpose' => 'Melihat pekerjaan audit unit yang paling perlu perhatian.',
                    'when' => 'Dibuka setelah login untuk melihat progres, deadline, klarifikasi, visitasi, dan temuan.',
                    'steps' => [
                        'Cek kartu Total Instrumen, Belum Diisi, Perlu Klarifikasi, dan Sudah Final.',
                        'Perhatikan progress bar kesiapan evaluasi diri.',
                        'Lihat jadwal visitasi dan daftar temuan aktif jika ada.',
                        'Klik kartu atau daftar untuk masuk ke halaman terkait.',
                    ],
                    'result' => 'Unit tahu pekerjaan apa yang harus diselesaikan lebih dulu.',
                ],
                [
                    'title' => 'Profil Unit',
                    'url' => route('auditee.unit-profile'),
                    'purpose' => 'Mengenali data unit yang terhubung dengan akun auditee.',
                    'when' => 'Dipakai saat ingin memastikan akun sudah berada pada unit yang benar.',
                    'steps' => [
                        'Buka Profil Unit.',
                        'Periksa nama unit, kode, jenis unit, dan informasi pimpinan.',
                        'Hubungi admin jika ada data unit yang tidak sesuai.',
                    ],
                    'result' => 'Data audit tercatat pada unit yang benar.',
                ],
                [
                    'title' => 'Evaluasi Diri',
                    'url' => route('auditee.self-evaluations'),
                    'purpose' => 'Menjawab instrumen audit berdasarkan kondisi nyata unit.',
                    'when' => 'Dikerjakan sejak periode audit aktif sampai batas evaluasi diri.',
                    'steps' => [
                        'Buka Evaluasi Diri.',
                        'Pilih standar lalu klik instrumen yang ingin diisi.',
                        'Baca pertanyaan, target kriteria, panduan pengisian, dan bukti yang diperlukan.',
                        'Isi jawaban naratif, realisasi, kendala, analisis gap, dan rencana perbaikan awal.',
                        'Simpan Draft jika belum selesai atau Tandai Selesai jika jawaban sudah siap dikirim.',
                        'Finalisasi evaluasi diri jika semua instrumen wajib sudah selesai.',
                    ],
                    'result' => 'Jawaban unit terkirim ke auditor untuk desk evaluation.',
                ],
                [
                    'title' => 'Bukti Dokumen',
                    'url' => route('auditee.documents'),
                    'purpose' => 'Mengelola file atau tautan pendukung jawaban audit.',
                    'when' => 'Dipakai saat instrumen membutuhkan dokumen, tautan, foto, atau bukti lain.',
                    'steps' => [
                        'Klik Unggah Bukti Baru.',
                        'Isi nama dokumen, jenis, tahun, standar, dan instrumen terkait.',
                        'Pilih sumber: file atau tautan.',
                        'Untuk file, gunakan format yang diizinkan seperti PDF, DOCX, XLSX, JPG, atau PNG.',
                        'Simpan dan pastikan bukti muncul pada instrumen yang sesuai.',
                    ],
                    'result' => 'Bukti tersedia untuk diperiksa auditor.',
                ],
                [
                    'title' => 'Klarifikasi Auditor',
                    'url' => route('auditee.clarifications'),
                    'purpose' => 'Menjawab pertanyaan auditor terkait instrumen tertentu.',
                    'when' => 'Dipakai jika auditor meminta penjelasan tambahan atau bukti pelengkap.',
                    'steps' => [
                        'Buka daftar Klarifikasi Auditor.',
                        'Klik klarifikasi yang masih terbuka atau dibuka kembali.',
                        'Baca pertanyaan auditor dan instrumen terkait.',
                        'Balas dengan penjelasan yang ringkas dan jelas.',
                        'Lampirkan dokumen tambahan jika diperlukan.',
                        'Klik Tandai Sudah Dijawab setelah respons lengkap.',
                    ],
                    'result' => 'Auditor mendapat penjelasan tambahan untuk melanjutkan pemeriksaan.',
                ],
                [
                    'title' => 'Jadwal Visitasi',
                    'url' => route('auditee.visit-schedules'),
                    'purpose' => 'Melihat jadwal visitasi dan mengonfirmasi kehadiran unit.',
                    'when' => 'Dipakai setelah auditor menetapkan jadwal visitasi.',
                    'steps' => [
                        'Buka Jadwal Visitasi.',
                        'Periksa tanggal, waktu, tipe visitasi, lokasi atau tautan, dan agenda.',
                        'Klik Konfirmasi Kehadiran jika jadwal sudah diterima.',
                        'Unggah dokumen tambahan jika diminta sebelum atau saat visitasi.',
                        'Unduh berita acara setelah auditor mengirimkannya.',
                    ],
                    'result' => 'Visitasi tercatat terkonfirmasi dan berita acara dapat diarsipkan.',
                ],
                [
                    'title' => 'Tindak Lanjut Temuan',
                    'url' => route('auditee.findings-followups'),
                    'purpose' => 'Menanggapi temuan audit dengan rencana perbaikan dan bukti pelaksanaan.',
                    'when' => 'Dipakai setelah auditor memfinalisasi temuan dan mengirimkannya ke unit.',
                    'steps' => [
                        'Buka daftar temuan aktif.',
                        'Klik nomor temuan untuk melihat kondisi aktual, kriteria, bukti objektif, rekomendasi, dan target penyelesaian.',
                        'Isi rencana tindakan, penanggung jawab, target, indikator keberhasilan, progres, dan catatan.',
                        'Unggah bukti perbaikan.',
                        'Ajukan tindak lanjut untuk diverifikasi auditor.',
                        'Perbaiki kembali jika auditor memberi keputusan perlu perbaikan.',
                    ],
                    'result' => 'Temuan dapat disetujui auditor dan ditutup.',
                ],
                [
                    'title' => 'Laporan Unit',
                    'url' => route('auditee.reports'),
                    'purpose' => 'Mengunduh laporan audit milik unit sendiri.',
                    'when' => 'Dipakai saat membutuhkan rekap evaluasi diri, hasil desk evaluation, visitasi, temuan, tindak lanjut, atau laporan hasil audit unit.',
                    'steps' => [
                        'Pilih periode audit.',
                        'Pilih jenis laporan.',
                        'Gunakan Pratinjau untuk membaca laporan di browser.',
                        'Gunakan Unduh untuk menyimpan PDF atau Excel.',
                    ],
                    'result' => 'Unit memiliki dokumen audit untuk arsip dan tindak lanjut mutu.',
                ],
            ],
            'notes' => [
                'Auditee hanya dapat mengakses data unitnya sendiri.',
                'Jawaban tidak dapat diedit setelah final kecuali ada klarifikasi atau akses dibuka kembali.',
                'Perhatikan batas evaluasi diri dan target tindak lanjut agar tidak terlambat.',
            ],
        ]);
    }
}

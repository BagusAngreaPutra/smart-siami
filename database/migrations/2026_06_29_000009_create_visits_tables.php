<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->unique()->constrained('audit_assignments')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->enum('tipe', ['lapangan', 'daring'])->default('lapangan');
            $table->text('lokasi_atau_tautan')->nullable();
            $table->text('agenda')->nullable();
            $table->text('catatan_wawancara')->nullable();
            $table->text('catatan_observasi')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->enum('status', ['belum_dijadwalkan', 'terjadwal', 'selesai', 'berita_acara_disetujui'])->default('belum_dijadwalkan')->index();
            $table->boolean('konfirmasi_auditee')->default(false);
            $table->timestamp('waktu_konfirmasi_auditee')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->text('nama_peserta');
            $table->text('jabatan')->nullable();
            $table->enum('tipe', ['auditor', 'auditee', 'lainnya']);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('visit_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->string('nama_file');
            $table->enum('tipe_sumber', ['file', 'tautan']);
            $table->string('path_file')->nullable();
            $table->string('url_tautan')->nullable();
            $table->foreignId('diunggah_oleh')->constrained('users');
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_attachments');
        Schema::dropIfExists('visit_participants');
        Schema::dropIfExists('visits');
    }
};

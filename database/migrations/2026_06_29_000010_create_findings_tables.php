<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_temuan')->nullable()->unique();
            $table->foreignId('assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->foreignId('standard_id')->constrained('standards');
            $table->foreignId('instrument_id')->constrained('instruments');
            $table->enum('kategori', ['observasi', 'peluang_peningkatan', 'minor', 'mayor']);
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->text('kondisi_aktual');
            $table->text('kriteria');
            $table->text('bukti_objektif');
            $table->text('akar_masalah_awal')->nullable();
            $table->text('rekomendasi_auditor');
            $table->date('target_penyelesaian');
            $table->enum('status', ['draft', 'aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'ditutup', 'terlambat', 'dibatalkan'])->default('draft')->index();
            $table->text('alasan_pembatalan')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->foreignId('difinalisasi_oleh')->nullable()->constrained('users');
            $table->timestamp('waktu_finalisasi')->nullable();
            $table->timestamps();
        });

        Schema::create('finding_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finding_id')->constrained('findings')->cascadeOnDelete();
            $table->string('dari_status')->nullable();
            $table->string('ke_status')->nullable();
            $table->string('field')->nullable();
            $table->text('nilai_lama')->nullable();
            $table->text('nilai_baru')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finding_status_histories');
        Schema::dropIfExists('findings');
    }
};

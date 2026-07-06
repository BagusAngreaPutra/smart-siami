<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained('instruments')->cascadeOnDelete();
            $table->text('jawaban_naratif')->nullable();
            $table->text('realisasi')->nullable();
            $table->text('target')->nullable();
            $table->text('kendala')->nullable();
            $table->text('analisis_gap')->nullable();
            $table->text('rencana_perbaikan_awal')->nullable();
            $table->enum('status', ['belum_diisi', 'draft', 'dikirim', 'perlu_klarifikasi', 'final'])->default('belum_diisi')->index();
            $table->timestamps();

            $table->unique(['assignment_id', 'instrument_id']);
        });

        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('self_assessment_id')->nullable()->constrained('self_assessments')->nullOnDelete();
            $table->unsignedBigInteger('follow_up_id')->nullable();
            $table->string('nama_dokumen');
            $table->text('jenis_dokumen')->nullable();
            $table->enum('tipe_sumber', ['file', 'tautan']);
            $table->string('path_file')->nullable();
            $table->string('url_tautan')->nullable();
            $table->unsignedBigInteger('ukuran_file')->nullable();
            $table->year('tahun_dokumen')->nullable();
            $table->text('deskripsi')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->text('instrumen_terkait')->nullable();
            $table->json('instrument_ids')->nullable();
            $table->enum('status_verifikasi', ['belum_diperiksa', 'valid', 'perlu_klarifikasi'])->default('belum_diperiksa')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidences');
        Schema::dropIfExists('self_assessments');
    }
};

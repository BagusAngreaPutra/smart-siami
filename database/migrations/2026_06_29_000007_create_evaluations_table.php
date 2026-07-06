<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained('instruments')->cascadeOnDelete();
            $table->foreignId('self_assessment_id')->constrained('self_assessments')->cascadeOnDelete();
            $table->decimal('skor', 8, 2)->nullable();
            $table->enum('status_bukti', ['belum_diperiksa', 'valid', 'perlu_klarifikasi', 'tidak_tersedia'])->default('belum_diperiksa');
            $table->text('catatan_auditor')->nullable();
            $table->boolean('usulan_temuan')->default(false);
            $table->text('rekomendasi_awal')->nullable();
            $table->enum('status_pemeriksaan', ['belum_dimulai', 'berlangsung', 'menunggu_klarifikasi', 'final'])->default('belum_dimulai')->index();
            $table->foreignId('diperiksa_oleh')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['assignment_id', 'instrument_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};

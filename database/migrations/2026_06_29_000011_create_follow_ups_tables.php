<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finding_id')->constrained('findings')->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->text('rencana_tindakan');
            $table->text('penanggung_jawab');
            $table->date('target_penyelesaian');
            $table->text('indikator_keberhasilan');
            $table->enum('progres', ['belum_mulai', 'berlangsung', 'selesai', 'terkendala'])->default('belum_mulai');
            $table->text('kendala')->nullable();
            $table->text('catatan_auditee')->nullable();
            $table->enum('status', ['belum_dibuat', 'draft', 'diajukan', 'perlu_perbaikan', 'disetujui'])->default('draft')->index();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });

        Schema::create('follow_up_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follow_up_id')->constrained('follow_ups')->cascadeOnDelete();
            $table->foreignId('verifikator_id')->constrained('users');
            $table->enum('keputusan', ['disetujui', 'perlu_perbaikan', 'ditolak']);
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('waktu_verifikasi');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_verifications');
        Schema::dropIfExists('follow_ups');
    }
};

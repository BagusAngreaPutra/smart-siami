<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_periods', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tahun_akademik');
            $table->enum('jenis_audit', ['reguler', 'akademik', 'nonakademik', 'tindak_lanjut', 'khusus']);
            $table->date('tanggal_mulai');
            $table->date('batas_evaluasi_diri');
            $table->date('batas_desk_evaluation');
            $table->date('visitasi_mulai')->nullable();
            $table->date('visitasi_selesai')->nullable();
            $table->date('batas_tindak_lanjut');
            $table->enum('status', ['draft', 'aktif', 'ditutup', 'diarsipkan'])->default('draft')->index();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_periods');
    }
};

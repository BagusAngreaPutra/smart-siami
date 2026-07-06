<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_period_id')->constrained('audit_periods')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('lead_auditor_id')->constrained('users');
            $table->text('catatan_penugasan')->nullable();
            $table->date('tanggal_desk_evaluation')->nullable();
            $table->date('jadwal_visitasi')->nullable();
            $table->enum('status', ['aktif', 'dibatalkan'])->default('aktif')->index();
            $table->timestamps();
        });

        Schema::create('assignment_auditors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->foreignId('auditor_id')->constrained('users');
            $table->enum('peran_dalam_tim', ['lead', 'anggota'])->default('anggota');
            $table->timestamps();

            $table->unique(['assignment_id', 'auditor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_auditors');
        Schema::dropIfExists('audit_assignments');
    }
};

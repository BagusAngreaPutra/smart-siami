<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clarifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained('instruments')->cascadeOnDelete();
            $table->foreignId('dibuka_oleh')->constrained('users');
            $table->enum('status', ['terbuka', 'dijawab', 'selesai', 'dibuka_kembali'])->default('terbuka')->index();
            $table->timestamps();
        });

        Schema::create('clarification_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clarification_id')->constrained('clarifications')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->constrained('users');
            $table->text('isi_pesan');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('clarification_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clarification_id')->constrained('clarifications')->cascadeOnDelete();
            $table->string('nama_dokumen');
            $table->enum('tipe_sumber', ['file', 'tautan']);
            $table->string('path_file')->nullable();
            $table->string('url_tautan')->nullable();
            $table->foreignId('diunggah_oleh')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clarification_evidences');
        Schema::dropIfExists('clarification_messages');
        Schema::dropIfExists('clarifications');
    }
};

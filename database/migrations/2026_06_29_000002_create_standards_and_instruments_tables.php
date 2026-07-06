<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standards', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->text('target')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->foreignId('standard_id')->constrained('standards')->cascadeOnDelete();
            $table->string('nama_indikator')->nullable();
            $table->text('pertanyaan');
            $table->enum('jenis_jawaban', ['narasi', 'angka', 'pilihan', 'skor', 'unggah_file', 'kombinasi']);
            $table->text('target_kriteria');
            $table->decimal('bobot', 8, 2)->nullable();
            $table->text('panduan_pengisian')->nullable();
            $table->text('bukti_diperlukan');
            $table->json('opsi_jawaban')->nullable();
            $table->integer('skor_min')->nullable();
            $table->integer('skor_max')->nullable();
            $table->json('kombinasi_jawaban')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0)->index();
            $table->timestamps();

            $table->unique(['standard_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruments');
        Schema::dropIfExists('standards');
    }
};

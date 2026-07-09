<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instruments', function (Blueprint $table): void {
            $table->string('accreditation_body')->nullable()->index();
            $table->string('sasaran_strategi_kode')->nullable();
            $table->string('ikss_kode')->nullable();
            $table->string('indikator_kegiatan_kode')->nullable();
            $table->string('kode_indikator_akreditasi')->nullable();
            $table->string('standar_universitas')->nullable();
            $table->string('aspek_indikator')->nullable();
            $table->json('matriks_skor')->nullable();
            $table->string('sumber_template')->nullable();
            $table->timestamp('imported_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('instruments', function (Blueprint $table): void {
            $table->dropColumn([
                'accreditation_body',
                'sasaran_strategi_kode',
                'ikss_kode',
                'indikator_kegiatan_kode',
                'kode_indikator_akreditasi',
                'standar_universitas',
                'aspek_indikator',
                'matriks_skor',
                'sumber_template',
                'imported_at',
            ]);
        });
    }
};

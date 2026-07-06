<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('code', 'kode');
            $table->renameColumn('name', 'nama');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->enum('jenis_unit', ['fakultas', 'prodi', 'unit_kerja', 'lainnya'])->default('prodi')->after('nama');
            $table->string('fakultas_induk')->nullable()->after('jenis_unit');
            $table->string('nama_pimpinan')->nullable()->after('fakultas_induk');
            $table->string('email')->nullable()->after('nama_pimpinan');
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_unit',
                'fakultas_induk',
                'nama_pimpinan',
                'email',
                'phone',
                'is_active',
            ]);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('kode', 'code');
            $table->renameColumn('nama', 'name');
        });
    }
};

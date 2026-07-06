<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete()->index();
            $table->string('tipe')->nullable()->after('user_id')->index();
            $table->string('judul')->nullable()->after('tipe');
            $table->text('isi')->nullable()->after('judul');
            $table->string('url_tujuan')->nullable()->after('isi');
            $table->string('objek_tipe')->nullable()->after('url_tujuan');
            $table->unsignedBigInteger('objek_id')->nullable()->after('objek_tipe');
            $table->boolean('is_read')->default(false)->after('objek_id')->index();
            $table->timestamp('archived_at')->nullable()->after('is_read')->index();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'tipe',
                'judul',
                'isi',
                'url_tujuan',
                'objek_tipe',
                'objek_id',
                'is_read',
                'archived_at',
            ]);
        });
    }
};

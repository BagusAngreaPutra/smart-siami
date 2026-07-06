<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('profile_photo_focus_x')->default(50)->after('profile_photo_path');
            $table->unsignedTinyInteger('profile_photo_focus_y')->default(50)->after('profile_photo_focus_x');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['profile_photo_focus_x', 'profile_photo_focus_y']);
        });
    }
};

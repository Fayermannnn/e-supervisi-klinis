<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * instrumen_id sengaja tidak ada di migration sesi_supervisi awal
     * (Sprint 3) karena instrumen_observasi belum ada saat itu. Ditambahkan
     * di sini begitu Modul 5 tersedia (Sprint 4), sesuai ERD baseline:
     * "Terkunci setelah status Observasi (BR-06)".
     */
    public function up(): void
    {
        Schema::table('sesi_supervisi', function (Blueprint $table) {
            $table->foreignUuid('instrumen_id')->nullable()
                ->constrained('instrumen_observasi')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sesi_supervisi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instrumen_id');
        });
    }
};

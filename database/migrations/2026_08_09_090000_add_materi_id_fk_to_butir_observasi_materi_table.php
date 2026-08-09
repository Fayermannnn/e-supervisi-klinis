<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * materi_id sengaja tidak diberi FK di migration Sprint 4
     * (butir_observasi_materi) karena materi_pengembangan belum ada saat
     * itu. Ditambahkan di sini begitu Modul 14 tersedia (Sprint 8B),
     * sesuai Addendum 02 Bagian 3.2.
     */
    public function up(): void
    {
        Schema::table('butir_observasi_materi', function (Blueprint $table) {
            $table->foreign('materi_id')->references('id')->on('materi_pengembangan')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('butir_observasi_materi', function (Blueprint $table) {
            $table->dropForeign(['materi_id']);
        });
    }
};

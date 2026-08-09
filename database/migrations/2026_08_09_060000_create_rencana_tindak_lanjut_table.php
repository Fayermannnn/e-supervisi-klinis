<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * kategori mengikuti 4 kompetensi guru (Permendiknas No. 16/2007,
     * dirujuk SDD Bagian 4): pedagogik, kepribadian, sosial, profesional.
     * Kolom polos (bukan FK) supaya siap dipakai BR-09a (Addendum 02:
     * "rekomendasi materi PD dipicu kecocokan kategori RTL <-> katalog
     * materi") begitu Modul 14 dibangun Sprint 8B - tidak perlu migration
     * tambahan seperti instrumen_id di sesi_supervisi, karena ini bukan
     * referensi ke tabel yang belum ada.
     */
    public function up(): void
    {
        Schema::create('rencana_tindak_lanjut', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sesi_id')->unique()->constrained('sesi_supervisi')->restrictOnDelete();
            $table->text('deskripsi');
            $table->date('target_waktu');
            $table->string('kategori')->nullable();
            $table->string('status')->default('belum');
            $table->timestamp('tanggal_diisi');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_tindak_lanjut');
    }
};

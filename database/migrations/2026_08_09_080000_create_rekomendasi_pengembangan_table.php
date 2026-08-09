<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * sumber diperluas jadi 3 nilai (Addendum 02, Bagian 3.2, BR-09 Dual
     * Trigger): manual | otomatis_rtl (BR-09a) | otomatis_observasi
     * (BR-09b) - membedakan asal rekomendasi saat audit/pelaporan.
     * sesi_id tetap dipakai untuk BR-09b (terikat ke sesi_supervisi,
     * tanpa FK terpisah ke butir/hasil_observasi individual, sesuai
     * Addendum 02).
     */
    public function up(): void
    {
        Schema::create('rekomendasi_pengembangan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengguna_id')->constrained('pengguna')->restrictOnDelete();
            $table->foreignUuid('sesi_id')->nullable()->constrained('sesi_supervisi')->restrictOnDelete();
            $table->foreignUuid('materi_id')->constrained('materi_pengembangan')->restrictOnDelete();
            $table->string('sumber');
            $table->string('status')->default('belum');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_pengembangan');
    }
};

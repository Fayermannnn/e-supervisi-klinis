<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 3 field terstruktur (Sprint 7 backlog: "Field terpisah, bukan 1
     * textarea") - SDD tidak menamai field secara eksplisit; kekuatan/
     * area_pengembangan/rekomendasi mengikuti struktur konferensi
     * pasca-observasi standar pada literatur supervisi klinis yang
     * dirujuk SDD (Cogan, Goldhammer). pendekatan_dipakai (BR-10) dan
     * terlambat (BR-03, dicatat bukan diblokir) khusus Developmental
     * Supervision. refleksi_guru diisi belakangan lewat endpoint
     * terpisah (US-06), karenanya nullable.
     */
    public function up(): void
    {
        Schema::create('umpan_balik', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sesi_id')->unique()->constrained('sesi_supervisi')->restrictOnDelete();
            $table->text('kekuatan');
            $table->text('area_pengembangan');
            $table->text('rekomendasi');
            $table->text('refleksi_guru')->nullable();
            $table->string('pendekatan_dipakai')->nullable();
            $table->boolean('terlambat')->default(false);
            $table->timestamp('tanggal_diisi');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umpan_balik');
    }
};

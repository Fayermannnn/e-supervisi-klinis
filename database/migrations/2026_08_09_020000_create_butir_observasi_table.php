<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('butir_observasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('instrumen_id')->constrained('instrumen_observasi')->restrictOnDelete();
            /**
             * kode & dimensi: pengelompokan 10 dimensi x 2 sub-indikator
             * (Addendum 02 Bagian 2.1, mis. kode "A1" pada dimensi
             * "A. Pembukaan Pembelajaran").
             */
            $table->string('kode');
            $table->string('dimensi');
            $table->text('teks');
            $table->text('definisi_operasional')->nullable();
            $table->decimal('bobot', 5, 2);
            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Pemetaan butir->materi PD (Addendum 02, Bagian 3.2 - BR-09b
         * Dual Trigger). materi_id BELUM diberi FK di sini karena tabel
         * materi_pengembangan baru dibuat Sprint 8B; FK ditambahkan lewat
         * migration baru saat itu, bukan mengedit migration ini (aturan
         * proyek: jangan edit migration yang sudah di-merge).
         */
        Schema::create('butir_observasi_materi', function (Blueprint $table) {
            $table->foreignUuid('butir_observasi_id')->constrained('butir_observasi')->restrictOnDelete();
            $table->uuid('materi_id');

            $table->primary(['butir_observasi_id', 'materi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('butir_observasi_materi');
        Schema::dropIfExists('butir_observasi');
    }
};

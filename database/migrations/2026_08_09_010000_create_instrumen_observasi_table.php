<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrumen_observasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('versi');
            $table->string('nama');
            /**
             * Skala fleksibel per instrumen (Dok 04 Tahap 4), meski Addendum
             * 02 mengunci skala saat ini ke 1-4 ("bukan skala lain"). Kolom
             * dipertahankan untuk fleksibilitas versi instrumen mendatang.
             */
            $table->unsignedTinyInteger('skor_min')->default(1);
            $table->unsignedTinyInteger('skor_maks')->default(4);
            $table->boolean('terkunci')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumen_observasi');
    }
};

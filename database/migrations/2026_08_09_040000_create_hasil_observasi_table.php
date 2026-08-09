<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_observasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sesi_id')->constrained('sesi_supervisi')->restrictOnDelete();
            $table->foreignUuid('butir_id')->constrained('butir_observasi')->restrictOnDelete();
            $table->unsignedTinyInteger('skor');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sesi_id', 'butir_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_observasi');
    }
};

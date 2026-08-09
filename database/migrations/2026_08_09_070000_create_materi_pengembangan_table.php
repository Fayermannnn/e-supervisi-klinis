<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * kategori selaras 4 kompetensi guru (SDD Bagian I.8), kosakata sama
     * dengan rencana_tindak_lanjut.kategori supaya BR-09a bisa mencocokkan
     * keduanya secara langsung.
     */
    public function up(): void
    {
        Schema::create('materi_pengembangan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('kategori');
            $table->text('tautan_atau_deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_pengembangan');
    }
};

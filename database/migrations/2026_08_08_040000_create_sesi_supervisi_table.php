<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_supervisi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sekolah_id')->constrained('sekolah')->restrictOnDelete();
            $table->foreignUuid('guru_id')->constrained('pengguna')->restrictOnDelete();
            $table->foreignUuid('supervisor_id')->constrained('pengguna')->restrictOnDelete();
            $table->string('tipe_supervisor');
            $table->string('status')->default('draft');
            $table->date('tanggal');
            $table->text('fokus_observasi')->nullable();
            $table->string('level_perkembangan_guru')->nullable();
            $table->string('pendekatan_disarankan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * BR-02 (Dok 04, Bagian 8 Constraint) dan integritas status ditegakkan
         * di Service layer (defense pertama) DAN sebagai DB CHECK (defense
         * kedua, sesuai Sprint 3 backlog: "CHECK constraint guru_id <>
         * supervisor_id - Ditolak di level DB"). Raw SQL khusus Postgres;
         * test lokal jalan di sqlite in-memory (lihat phpunit.xml), sehingga
         * blok ini dilewati di driver selain pgsql dan BR-02 tetap teruji
         * lewat Service layer saja pada test suite.
         */
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sesi_supervisi ADD CONSTRAINT sesi_supervisi_guru_supervisor_berbeda CHECK (guru_id <> supervisor_id)');
            DB::statement("ALTER TABLE sesi_supervisi ADD CONSTRAINT sesi_supervisi_status_valid CHECK (status IN ('draft', 'dijadwalkan', 'pra_observasi', 'observasi', 'dianalisis', 'umpan_balik', 'rtl', 'selesai'))");
            DB::statement("ALTER TABLE sesi_supervisi ADD CONSTRAINT sesi_supervisi_tipe_supervisor_valid CHECK (tipe_supervisor IN ('internal', 'eksternal'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_supervisi');
    }
};

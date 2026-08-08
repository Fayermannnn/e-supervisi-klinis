<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('aksi');
            $table->text('deskripsi')->nullable();
            $table->string('model_type')->nullable();
            $table->uuid('model_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        /**
         * Append-only (Bagian 13 Security, SDD): REVOKE saja tidak cukup karena
         * pemilik tabel di Postgres selalu bisa bypass ACL miliknya sendiri.
         * Trigger BEFORE UPDATE/DELETE menegakkan larangan ini di level DB,
         * terlepas dari role mana yang menjalankan query.
         *
         * PL/pgSQL khusus Postgres; test lokal jalan di sqlite in-memory
         * (lihat phpunit.xml), jadi blok ini dilewati di driver selain pgsql.
         */
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE OR REPLACE FUNCTION audit_log_larang_ubah_hapus()
                RETURNS TRIGGER AS $$
                BEGIN
                    RAISE EXCEPTION 'audit_log bersifat append-only: UPDATE dan DELETE tidak diizinkan';
                END;
                $$ LANGUAGE plpgsql;

                CREATE TRIGGER audit_log_cegah_update
                BEFORE UPDATE ON audit_log
                FOR EACH ROW EXECUTE FUNCTION audit_log_larang_ubah_hapus();

                CREATE TRIGGER audit_log_cegah_delete
                BEFORE DELETE ON audit_log
                FOR EACH ROW EXECUTE FUNCTION audit_log_larang_ubah_hapus();
            SQL);

            DB::statement('REVOKE UPDATE, DELETE ON audit_log FROM PUBLIC');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS audit_log_cegah_update ON audit_log');
            DB::unprepared('DROP TRIGGER IF EXISTS audit_log_cegah_delete ON audit_log');
            DB::unprepared('DROP FUNCTION IF EXISTS audit_log_larang_ubah_hapus()');
        }

        Schema::dropIfExists('audit_log');
    }
};

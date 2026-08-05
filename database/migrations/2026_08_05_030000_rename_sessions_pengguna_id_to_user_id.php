<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel DatabaseSessionHandler menulis ke kolom bernama persis "user_id"
 * (hardcoded di framework), sehingga kolom sessions ini harus tetap
 * bernama "user_id" walau bertipe UUID mengikuti PK pengguna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->renameColumn('pengguna_id', 'user_id');
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'pengguna_id');
        });
    }
};

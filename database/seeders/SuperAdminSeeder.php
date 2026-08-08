<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Akun super_admin awal. Password acak, dicetak ke terminal saat seeding,
     * tidak pernah di-hardcode (lihat Bagian II.1 Panduan Instalasi).
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@sidoarjo.go.id');

        if (Pengguna::where('email', $email)->exists()) {
            $this->command?->warn("Akun super_admin ({$email}) sudah ada, dilewati.");

            return;
        }

        $password = Str::password(20);

        $pengguna = Pengguna::create([
            'nama' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'status_aktif' => true,
        ]);

        $pengguna->assignRole('super_admin');

        $this->command?->warn("Akun super_admin dibuat: {$email} / {$password}");
        $this->command?->warn('Salin password di atas sekarang, lalu segera ganti password + aktifkan 2FA.');
    }
}

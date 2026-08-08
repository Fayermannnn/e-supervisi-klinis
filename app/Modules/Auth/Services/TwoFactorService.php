<?php

namespace App\Modules\Auth\Services;

use App\Exceptions\AuthInvalidTwoFactorCodeException;
use App\Models\Pengguna;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    /**
     * Peran yang wajib 2FA (Bagian 13 Security, Software Design Document).
     */
    private const WAJIB_2FA = ['admin_dinas', 'super_admin'];

    private const JUMLAH_KODE_PEMULIHAN = 8;

    public function __construct(private readonly Google2FA $google2fa) {}

    public function wajib2fa(Pengguna $pengguna): bool
    {
        return $pengguna->hasAnyRole(self::WAJIB_2FA);
    }

    public function sudahAktif(Pengguna $pengguna): bool
    {
        return $pengguna->two_factor_confirmed_at !== null;
    }

    public function buatSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function qrCodeSvg(Pengguna $pengguna, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl(config('app.name'), $pengguna->email, $secret);

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($url);
    }

    /**
     * @return array<int, string> kode pemulihan (ditampilkan sekali ke pengguna)
     *
     * @throws AuthInvalidTwoFactorCodeException
     */
    public function aktifkan(Pengguna $pengguna, string $secret, string $kodeKonfirmasi): array
    {
        if (! $this->google2fa->verifyKey($secret, $kodeKonfirmasi)) {
            throw new AuthInvalidTwoFactorCodeException;
        }

        $kodePemulihan = collect(range(1, self::JUMLAH_KODE_PEMULIHAN))
            ->map(fn () => Str::random(10))
            ->all();

        $pengguna->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $kodePemulihan,
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $kodePemulihan;
    }

    /**
     * Menerima kode TOTP 6 digit ATAU salah satu kode pemulihan (sekali pakai).
     */
    public function verifikasiLogin(Pengguna $pengguna, string $kode): bool
    {
        if ($this->google2fa->verifyKey($pengguna->two_factor_secret, $kode)) {
            return true;
        }

        return $this->pakaiKodePemulihan($pengguna, $kode);
    }

    private function pakaiKodePemulihan(Pengguna $pengguna, string $kode): bool
    {
        $kodePemulihan = $pengguna->two_factor_recovery_codes ?? [];

        if (! in_array($kode, $kodePemulihan, true)) {
            return false;
        }

        $pengguna->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($kodePemulihan, [$kode])),
        ])->save();

        return true;
    }
}

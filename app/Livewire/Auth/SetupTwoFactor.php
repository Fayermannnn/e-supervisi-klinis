<?php

namespace App\Livewire\Auth;

use App\Exceptions\AuthInvalidTwoFactorCodeException;
use App\Models\Pengguna;
use App\Modules\Auth\Services\TwoFactorService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.centered')]
#[Title('Aktivasi Verifikasi Dua Langkah')]
class SetupTwoFactor extends Component
{
    public string $secret = '';

    public string $kodeKonfirmasi = '';

    /** @var array<int, string> */
    public array $kodePemulihan = [];

    public bool $sudahDikonfirmasi = false;

    public function mount(TwoFactorService $twoFactorService): void
    {
        /** @var Pengguna $pengguna */
        $pengguna = auth()->user();

        abort_unless($twoFactorService->wajib2fa($pengguna), 403);

        if ($twoFactorService->sudahAktif($pengguna)) {
            $this->sudahDikonfirmasi = true;

            return;
        }

        $this->secret = $twoFactorService->buatSecret();
    }

    public function konfirmasi(TwoFactorService $twoFactorService): void
    {
        $this->validate([
            'kodeKonfirmasi' => ['required', 'string'],
        ]);

        try {
            $this->kodePemulihan = $twoFactorService->aktifkan(auth()->user(), $this->secret, $this->kodeKonfirmasi);
        } catch (AuthInvalidTwoFactorCodeException) {
            $this->addError('kodeKonfirmasi', 'Kode verifikasi salah.');

            return;
        }

        $this->sudahDikonfirmasi = true;
    }

    public function render(TwoFactorService $twoFactorService)
    {
        return view('livewire.auth.setup-two-factor', [
            'qrCodeSvg' => (! $this->sudahDikonfirmasi && $this->secret !== '')
                ? $twoFactorService->qrCodeSvg(auth()->user(), $this->secret)
                : null,
        ]);
    }
}

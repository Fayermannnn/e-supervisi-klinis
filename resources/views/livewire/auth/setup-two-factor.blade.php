<div class="w-full max-w-md rounded-xl bg-white p-8 shadow">
    <h1 class="mb-2 text-xl font-semibold text-slate-800">Verifikasi Dua Langkah</h1>

    @if ($sudahDikonfirmasi && empty($kodePemulihan))
        <p class="mb-6 text-sm text-slate-500">Verifikasi dua langkah sudah aktif untuk akun ini.</p>
        <a href="{{ route('beranda') }}" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">
            Kembali ke Beranda
        </a>
    @elseif ($sudahDikonfirmasi)
        <p class="mb-4 text-sm text-green-700">Verifikasi dua langkah berhasil diaktifkan.</p>

        <p class="mb-2 text-sm font-medium text-slate-700">
            Simpan kode pemulihan ini di tempat aman. Setiap kode hanya bisa dipakai sekali sebagai pengganti
            aplikasi autentikator bila perangkat Anda hilang.
        </p>

        <div class="mb-6 grid grid-cols-2 gap-2 rounded-md bg-slate-50 p-4 font-mono text-sm text-slate-700">
            @foreach ($kodePemulihan as $kode)
                <span>{{ $kode }}</span>
            @endforeach
        </div>

        <a href="{{ route('beranda') }}" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">
            Saya Sudah Menyimpannya
        </a>
    @else
        <p class="mb-6 text-sm text-slate-500">
            Pindai kode QR ini dengan aplikasi autentikator (Google Authenticator, Authy, dsb), lalu masukkan
            kode 6 digit yang muncul untuk mengaktifkan.
        </p>

        <div class="mb-4 flex justify-center">
            {!! $qrCodeSvg !!}
        </div>

        <p class="mb-6 break-all text-center text-xs text-slate-400">{{ $secret }}</p>

        <form wire:submit="konfirmasi" class="space-y-4">
            <div>
                <label for="kodeKonfirmasi" class="block text-sm font-medium text-slate-700">Kode Verifikasi</label>
                <input type="text" id="kodeKonfirmasi" wire:model="kodeKonfirmasi" inputmode="numeric"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                @error('kodeKonfirmasi')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Aktifkan
            </button>
        </form>
    @endif
</div>

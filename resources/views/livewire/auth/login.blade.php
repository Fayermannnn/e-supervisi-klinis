<div class="w-full max-w-sm rounded-xl bg-white p-8 shadow">
    <h1 class="mb-6 text-xl font-semibold text-slate-800">Masuk</h1>

    <form wire:submit="login" class="space-y-4">
        @if (! $butuhTwoFactor)
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" id="email" wire:model="email" autocomplete="username"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                <input type="password" id="password" wire:model="password" autocomplete="current-password"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @else
            <p class="text-sm text-slate-500">Masukkan kode dari aplikasi autentikator Anda.</p>

            <div>
                <label for="twoFactorCode" class="block text-sm font-medium text-slate-700">Kode Verifikasi</label>
                <input type="text" id="twoFactorCode" wire:model="twoFactorCode" autocomplete="one-time-code" inputmode="numeric"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                @error('twoFactorCode')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        @error('form')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <button type="submit"
            class="w-full rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
            {{ $butuhTwoFactor ? 'Verifikasi' : 'Masuk' }}
        </button>
    </form>
</div>

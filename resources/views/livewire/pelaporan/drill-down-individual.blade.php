<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Detail Individual (Akses Berjustifikasi)</h1>
    <p class="text-sm text-slate-500">{{ $sesiSupervisi->guru->nama }} - {{ $sesiSupervisi->tanggal->format('d/m/Y') }}</p>

    @if (! $hasil)
        <div class="rounded-xl bg-white p-8 shadow">
            <p class="mb-4 text-sm text-slate-600">
                BR-08: akses ke data individual sesi ini wajib disertai justifikasi tertulis. Justifikasi
                akan tercatat pada Log Audit.
            </p>
            <form wire:submit="lihat" class="space-y-4">
                <div>
                    <label for="justifikasi" class="block text-sm font-medium text-slate-700">Justifikasi</label>
                    <textarea id="justifikasi" wire:model="justifikasi" rows="3"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
                    @error('justifikasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">
                    Lihat Detail
                </button>
            </form>
        </div>
    @else
        <div class="space-y-4 rounded-xl bg-white p-8 shadow" data-testid="hasil-drill-down">
            <div>
                <h2 class="text-xs font-medium uppercase text-slate-500">Skor Total</h2>
                <p class="text-sm text-slate-800">{{ $hasil['skor']['total'] }}</p>
            </div>
            @if ($hasil['umpan_balik'])
                <div>
                    <h2 class="text-xs font-medium uppercase text-slate-500">Kekuatan</h2>
                    <p class="text-sm text-slate-800">{{ $hasil['umpan_balik']['kekuatan'] }}</p>
                </div>
                <div>
                    <h2 class="text-xs font-medium uppercase text-slate-500">Area Pengembangan</h2>
                    <p class="text-sm text-slate-800">{{ $hasil['umpan_balik']['area_pengembangan'] }}</p>
                </div>
            @endif
            @if ($hasil['rtl'])
                <div>
                    <h2 class="text-xs font-medium uppercase text-slate-500">Rencana Tindak Lanjut</h2>
                    <p class="text-sm text-slate-800">{{ $hasil['rtl']['deskripsi'] }}</p>
                </div>
            @endif
            <p class="text-xs text-slate-400">Akses ini tercatat di Log Audit dengan justifikasi yang Anda berikan.</p>
        </div>
    @endif

    <a href="{{ route('app.sesi-supervisi.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Kembali</a>
</div>

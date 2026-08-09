<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Refleksi Guru</h1>

    <div class="space-y-4 rounded-xl bg-white p-6 shadow">
        <div>
            <h2 class="text-sm font-semibold text-slate-700">Kekuatan</h2>
            <p class="text-sm text-slate-600">{{ $kekuatan }}</p>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-slate-700">Area Pengembangan</h2>
            <p class="text-sm text-slate-600">{{ $area_pengembangan }}</p>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-slate-700">Rekomendasi</h2>
            <p class="text-sm text-slate-600">{{ $rekomendasi }}</p>
        </div>
    </div>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="refleksi_guru" class="block text-sm font-medium text-slate-700">Refleksi Anda</label>
            <textarea id="refleksi_guru" wire:model="refleksi_guru" rows="4"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"
                placeholder="Bagaimana pendapat Anda tentang umpan balik ini? Apa yang akan Anda coba selanjutnya?"></textarea>
            @error('refleksi_guru')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.sesi-supervisi.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Simpan Refleksi
            </button>
        </div>
    </form>
</div>

<div class="mx-auto max-w-lg space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Rekomendasikan Materi</h1>
    <p class="text-sm text-slate-500">Untuk {{ $sesiSupervisi->guru->nama }}</p>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="materi_id" class="block text-sm font-medium text-slate-700">Materi</label>
            <select id="materi_id" wire:model="materi_id"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                <option value="">Pilih materi</option>
                @foreach ($daftarMateri as $materi)
                    <option value="{{ $materi->id }}">{{ $materi->judul }} ({{ ucfirst($materi->kategori) }})</option>
                @endforeach
            </select>
            @error('materi_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.sesi-supervisi.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Kirim Rekomendasi
            </button>
        </div>
    </form>
</div>

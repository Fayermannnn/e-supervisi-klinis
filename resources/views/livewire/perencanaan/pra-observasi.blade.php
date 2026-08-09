<div class="mx-auto max-w-lg space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Pra-Observasi</h1>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="fokus_observasi" class="block text-sm font-medium text-slate-700">Fokus Observasi</label>
            <textarea id="fokus_observasi" wire:model="fokus_observasi" rows="4"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
            @error('fokus_observasi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="level_perkembangan_guru" class="block text-sm font-medium text-slate-700">
                Level Perkembangan Guru <span class="font-normal text-slate-400">(opsional)</span>
            </label>
            <select id="level_perkembangan_guru" wire:model="level_perkembangan_guru"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                <option value="">Tidak diisi</option>
                <option value="rendah">Rendah</option>
                <option value="sedang_rendah">Sedang-Rendah</option>
                <option value="sedang_tinggi">Sedang-Tinggi</option>
                <option value="tinggi">Tinggi</option>
            </select>
            @error('level_perkembangan_guru')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.sesi-supervisi.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Simpan
            </button>
        </div>
    </form>
</div>

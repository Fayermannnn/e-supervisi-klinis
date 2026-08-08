<div class="mx-auto max-w-lg space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Tambah Sekolah</h1>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="nama_sekolah" class="block text-sm font-medium text-slate-700">Nama Sekolah</label>
            <input type="text" id="nama_sekolah" wire:model="nama_sekolah"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('nama_sekolah')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="npsn" class="block text-sm font-medium text-slate-700">NPSN</label>
            <input type="text" id="npsn" wire:model="npsn"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('npsn')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="alamat" class="block text-sm font-medium text-slate-700">Alamat</label>
            <textarea id="alamat" wire:model="alamat" rows="3"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
            @error('alamat')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.sekolah.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Simpan
            </button>
        </div>
    </form>
</div>

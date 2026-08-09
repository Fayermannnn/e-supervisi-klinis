<div class="mx-auto max-w-lg space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Buat Instrumen</h1>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="versi" class="block text-sm font-medium text-slate-700">Versi</label>
            <input type="number" id="versi" wire:model="versi" min="1"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('versi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nama" class="block text-sm font-medium text-slate-700">Nama</label>
            <input type="text" id="nama" wire:model="nama"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('nama')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.instrumen.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Simpan
            </button>
        </div>
    </form>
</div>

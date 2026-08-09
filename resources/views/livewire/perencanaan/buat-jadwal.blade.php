<div class="mx-auto max-w-lg space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Buat Jadwal Sesi Supervisi</h1>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="guru_id" class="block text-sm font-medium text-slate-700">Guru</label>
            <select id="guru_id" wire:model="guru_id"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                <option value="">Pilih guru</option>
                @foreach ($daftarGuru as $guru)
                    <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                @endforeach
            </select>
            @error('guru_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tipe_supervisor" class="block text-sm font-medium text-slate-700">Tipe Supervisor</label>
            <select id="tipe_supervisor" wire:model="tipe_supervisor"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                <option value="internal">Internal</option>
                <option value="eksternal">Eksternal</option>
            </select>
            @error('tipe_supervisor')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tanggal" class="block text-sm font-medium text-slate-700">Tanggal</label>
            <input type="date" id="tanggal" wire:model="tanggal"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('tanggal')
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

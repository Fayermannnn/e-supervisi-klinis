<div class="mx-auto max-w-lg space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Ubah Pengguna</h1>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="nama" class="block text-sm font-medium text-slate-700">Nama</label>
            <input type="text" id="nama" wire:model="nama"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('nama')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input type="email" id="email" wire:model="email"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">
                Kata Sandi <span class="font-normal text-slate-400">(kosongkan jika tidak ingin mengubah)</span>
            </label>
            <input type="password" id="password" wire:model="password"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sekolah_id" class="block text-sm font-medium text-slate-700">Sekolah</label>
            <select id="sekolah_id" wire:model="sekolah_id"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                <option value="">- Tanpa Sekolah -</option>
                @foreach ($daftarSekolah as $item)
                    <option value="{{ $item->id }}">{{ $item->nama_sekolah }}</option>
                @endforeach
            </select>
            @error('sekolah_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nip_nuptk" class="block text-sm font-medium text-slate-700">NIP/NUPTK</label>
            <input type="text" id="nip_nuptk" wire:model="nip_nuptk"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('nip_nuptk')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="no_telepon" class="block text-sm font-medium text-slate-700">No. Telepon</label>
            <input type="text" id="no_telepon" wire:model="no_telepon"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            @error('no_telepon')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="status_aktif" wire:model="status_aktif" class="rounded border-slate-300">
            <label for="status_aktif" class="text-sm text-slate-700">Aktif</label>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.pengguna.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Simpan
            </button>
        </div>
    </form>
</div>

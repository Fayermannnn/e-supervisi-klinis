<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Katalog Materi Pengembangan</h1>
        @can('create', \App\Models\MateriPengembangan::class)
            <button type="button" wire:click="bukaForm"
                class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">
                Tambah Materi
            </button>
        @endcan
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($formTerbuka)
        <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
            <h2 class="text-lg font-semibold text-slate-800">{{ $editId ? 'Ubah Materi' : 'Tambah Materi' }}</h2>

            <div>
                <label for="judul" class="block text-sm font-medium text-slate-700">Judul</label>
                <input type="text" id="judul" wire:model="judul"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                @error('judul')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kategori" class="block text-sm font-medium text-slate-700">Kategori</label>
                <select id="kategori" wire:model="kategori"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    <option value="pedagogik">Pedagogik</option>
                    <option value="kepribadian">Kepribadian</option>
                    <option value="sosial">Sosial</option>
                    <option value="profesional">Profesional</option>
                </select>
            </div>

            <div>
                <label for="tautan_atau_deskripsi" class="block text-sm font-medium text-slate-700">
                    Tautan atau Deskripsi <span class="font-normal text-slate-400">(opsional)</span>
                </label>
                <textarea id="tautan_atau_deskripsi" wire:model="tautan_atau_deskripsi" rows="3"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" wire:click="$set('formTerbuka', false)" class="text-sm text-slate-600 hover:text-slate-900">
                    Batal
                </button>
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                    Simpan
                </button>
            </div>
        </form>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Judul</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Kategori</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($materi as $item)
                    <tr wire:key="materi-{{ $item->id }}">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item->judul }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ ucfirst($item->kategori) }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            @can('update', \App\Models\MateriPengembangan::class)
                                <button type="button" wire:click="edit('{{ $item->id }}')" class="text-slate-600 hover:text-slate-900">Ubah</button>
                            @endcan
                            @can('delete', \App\Models\MateriPengembangan::class)
                                <button type="button" wire:click="hapus('{{ $item->id }}')" wire:confirm="Hapus materi ini?"
                                    class="ml-3 text-red-600 hover:text-red-800">
                                    Hapus
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada materi pengembangan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $materi->links() }}
</div>

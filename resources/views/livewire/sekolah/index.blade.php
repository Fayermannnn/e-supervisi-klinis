<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Sekolah</h1>
        <a href="{{ route('app.sekolah.create') }}"
            class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">
            Tambah Sekolah
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Nama Sekolah</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">NPSN</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($sekolah as $item)
                    <tr wire:key="sekolah-{{ $item->id }}">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item->nama_sekolah }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item->npsn ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($item->status_aktif)
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('app.sekolah.edit', $item) }}" class="text-slate-600 hover:text-slate-900">Ubah</a>
                            <button type="button" wire:click="hapus('{{ $item->id }}')" wire:confirm="Hapus sekolah ini?"
                                class="ml-3 text-red-600 hover:text-red-800">
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data sekolah.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sekolah->links() }}
</div>

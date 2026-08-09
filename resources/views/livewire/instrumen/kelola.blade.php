<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">{{ $instrumen->nama }} (v{{ $instrumen->versi }})</h1>
            <p class="text-sm text-slate-500">Skala {{ $instrumen->skor_min }}-{{ $instrumen->skor_maks }}</p>
        </div>
        @if ($instrumen->terkunci)
            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">Aktif (terkunci)</span>
        @else
            @can('update', \App\Models\InstrumenObservasi::class)
                <button type="button" wire:click="aktifkan"
                    wire:confirm="Aktifkan instrumen ini? Setelah aktif, butir tidak bisa diedit lagi (BR-06)."
                    class="rounded-md bg-green-700 px-4 py-2 text-sm text-white hover:bg-green-800">
                    Aktifkan
                </button>
            @endcan
        @endif
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
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Dimensi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Teks</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Bobot</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($butir as $item)
                    <tr wire:key="butir-{{ $item->id }}">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item->kode }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item->dimensi }}</td>
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item->teks }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item->bobot }}%</td>
                        <td class="px-4 py-3 text-right text-sm">
                            @can('delete', \App\Models\InstrumenObservasi::class)
                                @unless ($instrumen->terkunci)
                                    <button type="button" wire:click="hapusButir('{{ $item->id }}')"
                                        wire:confirm="Hapus butir ini?"
                                        class="text-red-600 hover:text-red-800">
                                        Hapus
                                    </button>
                                @endunless
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada butir observasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('update', \App\Models\InstrumenObservasi::class)
        @unless ($instrumen->terkunci)
            <form wire:submit="tambahButir" class="space-y-4 rounded-xl bg-white p-8 shadow">
                <h2 class="text-lg font-semibold text-slate-800">Tambah Butir</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="kode" class="block text-sm font-medium text-slate-700">Kode</label>
                        <input type="text" id="kode" wire:model="kode" placeholder="mis. A1"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @error('kode')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="bobot" class="block text-sm font-medium text-slate-700">Bobot (%)</label>
                        <input type="number" id="bobot" wire:model="bobot" step="0.01" min="0" max="100"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @error('bobot')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="dimensi" class="block text-sm font-medium text-slate-700">Dimensi</label>
                    <input type="text" id="dimensi" wire:model="dimensi" placeholder="mis. A. Pembukaan Pembelajaran"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    @error('dimensi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="teks" class="block text-sm font-medium text-slate-700">Teks Butir</label>
                    <textarea id="teks" wire:model="teks" rows="2"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
                    @error('teks')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="definisi_operasional" class="block text-sm font-medium text-slate-700">
                        Definisi Operasional <span class="font-normal text-slate-400">(opsional)</span>
                    </label>
                    <textarea id="definisi_operasional" wire:model="definisi_operasional" rows="2"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                        Tambah Butir
                    </button>
                </div>
            </form>
        @endunless
    @endcan
</div>

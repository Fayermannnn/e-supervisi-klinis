<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Rencana Tindak Lanjut</h1>
    <p class="text-sm text-slate-500">{{ $sesiSupervisi->guru->nama }} - {{ $sesiSupervisi->tanggal->format('d/m/Y') }}</p>

    @if (session('status'))
        <div class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- Konteks umpan balik tetap tampil (Sprint 8 AC) - RTL ditulis sebagai
         respons langsung terhadap umpan balik, bukan terputus darinya. --}}
    @if ($sesiSupervisi->umpanBalik)
        <div class="space-y-3 rounded-xl bg-slate-50 p-6 ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-700">Konteks Umpan Balik</h2>
            <div>
                <h3 class="text-xs font-medium uppercase text-slate-500">Kekuatan</h3>
                <p class="text-sm text-slate-700">{{ $sesiSupervisi->umpanBalik->kekuatan }}</p>
            </div>
            <div>
                <h3 class="text-xs font-medium uppercase text-slate-500">Area Pengembangan</h3>
                <p class="text-sm text-slate-700">{{ $sesiSupervisi->umpanBalik->area_pengembangan }}</p>
            </div>
            <div>
                <h3 class="text-xs font-medium uppercase text-slate-500">Rekomendasi</h3>
                <p class="text-sm text-slate-700">{{ $sesiSupervisi->umpanBalik->rekomendasi }}</p>
            </div>
        </div>
    @endif

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        <div>
            <label for="deskripsi" class="block text-sm font-medium text-slate-700">Deskripsi Rencana Tindak Lanjut</label>
            <textarea id="deskripsi" wire:model="deskripsi" rows="4"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
            @error('deskripsi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="target_waktu" class="block text-sm font-medium text-slate-700">Target Waktu</label>
                <input type="date" id="target_waktu" wire:model="target_waktu"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                @error('target_waktu')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="kategori" class="block text-sm font-medium text-slate-700">Kategori</label>
                <select id="kategori" wire:model="kategori"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    <option value="">Pilih kategori</option>
                    <option value="pedagogik">Pedagogik</option>
                    <option value="kepribadian">Kepribadian</option>
                    <option value="sosial">Sosial</option>
                    <option value="profesional">Profesional</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.sesi-supervisi.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Simpan RTL
            </button>
        </div>
    </form>

    @if ($sudahAda)
        <div class="flex items-center justify-between rounded-xl bg-white p-6 shadow">
            <p class="text-sm text-slate-600">RTL sudah tersimpan. Sesi bisa ditandai selesai.</p>
            <button type="button" wire:click="selesaikan" wire:confirm="Tandai sesi ini selesai? Tindakan ini mengakhiri siklus supervisi."
                class="rounded-md bg-green-700 px-4 py-2 text-sm text-white hover:bg-green-800">
                Tandai Selesai
            </button>
        </div>
    @endif
</div>

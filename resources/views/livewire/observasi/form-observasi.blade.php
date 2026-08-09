<div class="mx-auto max-w-2xl space-y-6 pb-24">
    <div class="rounded-xl bg-white p-4 shadow">
        <x-progress-stepper :status="$sesiSupervisi->status" />
    </div>

    <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow">
        <div>
            <h1 class="text-lg font-semibold text-slate-800">Form Observasi</h1>
            <p class="text-sm text-slate-500">{{ $sesiSupervisi->guru->nama }} - {{ $sesiSupervisi->tanggal->format('d/m/Y') }}</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
            {{ $jumlahTerisi }}/{{ $jumlahButir }} terisi
        </span>
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    @forelse ($butirPerDimensi as $dimensi => $daftarButir)
        <div class="space-y-4 rounded-xl bg-white p-4 shadow">
            <h2 class="text-sm font-semibold text-slate-800">{{ $dimensi }}</h2>

            @foreach ($daftarButir as $butir)
                <div class="border-t border-slate-100 pt-4 first:border-t-0 first:pt-0">
                    <p class="mb-3 text-sm text-slate-700">
                        <span class="font-mono text-xs text-slate-400">{{ $butir->kode }}</span>
                        {{ $butir->teks }}
                    </p>
                    <x-radio-skor :model="'skor.'.$butir->id" :min="1" :max="4" />
                </div>
            @endforeach
        </div>
    @empty
        <div class="rounded-xl bg-white p-4 text-center text-sm text-slate-500 shadow">
            Tidak ada instrumen aktif untuk observasi ini.
        </div>
    @endforelse

    <div class="fixed inset-x-0 bottom-0 border-t border-slate-200 bg-white p-4">
        <div class="mx-auto flex max-w-2xl gap-3">
            <button type="button" wire:click="simpanDraft"
                class="flex-1 rounded-md border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Simpan Draft
            </button>
            <button type="button" wire:click="selesai" wire:confirm="Selesaikan observasi? Pastikan seluruh butir sudah terisi."
                class="flex-1 rounded-md bg-slate-800 px-4 py-3 text-sm font-medium text-white hover:bg-slate-700">
                Selesai
            </button>
        </div>
    </div>
</div>

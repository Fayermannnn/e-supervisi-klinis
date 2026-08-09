<div class="rounded-xl bg-white p-6 shadow">
    <h2 class="mb-4 text-sm font-semibold text-slate-800">Rekomendasi Pengembangan Diri</h2>

    @if ($rekomendasi->isEmpty())
        <p class="text-sm text-slate-500">Belum ada rekomendasi materi pengembangan.</p>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($rekomendasi as $item)
                <li wire:key="rekomendasi-{{ $item->id }}" class="flex items-center justify-between gap-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $item->materi->judul }}</p>
                        <p class="text-xs text-slate-400">{{ ucfirst($item->materi->kategori) }}</p>
                    </div>
                    <select wire:change="ubahStatus('{{ $item->id }}', $event.target.value)"
                        class="rounded-md border-slate-300 text-sm shadow-sm">
                        <option value="belum" @selected($item->status === 'belum')>Belum</option>
                        <option value="sedang" @selected($item->status === 'sedang')>Sedang Dikerjakan</option>
                        <option value="selesai" @selected($item->status === 'selesai')>Selesai</option>
                    </select>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<div class="relative" wire:poll.30s>
    <button type="button" wire:click="toggle" class="relative flex items-center text-slate-600 hover:text-slate-900">
        <span>Notifikasi</span>
        @if ($jumlahBelumDibaca > 0)
            <span class="ml-1 rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-semibold text-white">
                {{ $jumlahBelumDibaca }}
            </span>
        @endif
    </button>

    @if ($terbuka)
        <div class="absolute right-0 z-10 mt-2 w-80 rounded-xl bg-white shadow-lg ring-1 ring-slate-200">
            <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                @forelse ($terbaru as $item)
                    <li wire:key="notif-{{ $item->id }}" class="px-4 py-3 text-sm {{ $item->dibaca ? 'text-slate-400' : 'text-slate-800' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-medium">{{ $item->judul }}</p>
                                <p class="text-xs">{{ $item->pesan }}</p>
                            </div>
                            @unless ($item->dibaca)
                                <button type="button" wire:click="tandaiDibaca('{{ $item->id }}')"
                                    class="shrink-0 text-xs text-slate-500 hover:text-slate-800">
                                    Tandai dibaca
                                </button>
                            @endunless
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-slate-500">Belum ada notifikasi.</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>

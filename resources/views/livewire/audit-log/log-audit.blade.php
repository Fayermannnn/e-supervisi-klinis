<div class="space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Log Audit</h1>

    <div class="grid grid-cols-1 gap-4 rounded-xl bg-white p-4 shadow sm:grid-cols-3">
        <div>
            <label class="block text-xs font-medium uppercase text-slate-500">Aksi</label>
            <input type="text" wire:model.live.debounce.400ms="aksi" placeholder="Cari kode aksi..."
                class="mt-1 w-full rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium uppercase text-slate-500">Dari Tanggal</label>
            <input type="date" wire:model.live="dari" class="mt-1 w-full rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium uppercase text-slate-500">Sampai Tanggal</label>
            <input type="date" wire:model.live="sampai" class="mt-1 w-full rounded-md border-slate-300 text-sm">
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Pengguna</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Aksi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Deskripsi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($logs as $log)
                    <tr wire:key="audit-{{ $log->id }}">
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $log->pengguna?->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $log->aksi }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $log->deskripsi ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Tidak ada log yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between">
        <button type="button" wire:click="muatSebelumnya" @disabled(! $cursorSebelumnya)
            class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 hover:enabled:bg-slate-50">
            Sebelumnya
        </button>
        <button type="button" wire:click="muatBerikutnya" @disabled(! $cursorBerikutnya)
            class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 hover:enabled:bg-slate-50">
            Berikutnya
        </button>
    </div>
</div>

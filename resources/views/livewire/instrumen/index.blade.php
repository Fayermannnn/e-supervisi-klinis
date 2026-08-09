<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Instrumen Observasi</h1>
        @can('create', \App\Models\InstrumenObservasi::class)
            <a href="{{ route('app.instrumen.create') }}"
                class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">
                Buat Instrumen
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Versi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Jumlah Butir</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($instrumen as $item)
                    <tr wire:key="instrumen-{{ $item->id }}">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item->versi }}</td>
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item->nama }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item->butir_count }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($item->terkunci)
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-700">Aktif (terkunci)</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('app.instrumen.kelola', $item) }}" class="text-slate-600 hover:text-slate-900">Kelola</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada instrumen observasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $instrumen->links() }}
</div>

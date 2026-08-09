<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Sesi Supervisi</h1>
        @can('create', \App\Models\SesiSupervisi::class)
            <a href="{{ route('app.sesi-supervisi.create') }}"
                class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">
                Buat Jadwal
            </a>
        @endcan
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
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Guru</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Supervisor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($sesi as $item)
                    <tr wire:key="sesi-{{ $item->id }}">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item->guru->nama }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item->supervisor->nama }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $item->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            @can('update', $item)
                                @if ($item->status === 'dijadwalkan')
                                    <a href="{{ route('app.sesi-supervisi.pra-observasi', $item) }}"
                                        class="text-slate-600 hover:text-slate-900">Isi Pra-Observasi</a>
                                @elseif (in_array($item->status, ['pra_observasi', 'observasi']))
                                    <a href="{{ route('app.sesi-supervisi.observasi', $item) }}"
                                        class="text-slate-600 hover:text-slate-900">Isi Observasi</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada sesi supervisi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sesi->links() }}
</div>

<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Ringkasan Skor</h1>
        <span class="rounded-full bg-slate-800 px-4 py-2 text-lg font-semibold text-white">{{ $total }}</span>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Dimensi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Skor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Bobot</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Skor Tertimbang</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($breakdown as $item)
                    <tr wire:key="skor-{{ $item['butir_id'] }}">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item['kode'] }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item['dimensi'] }}</td>
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item['skor'] }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item['bobot'] }}%</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $item['skor_tertimbang'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada hasil observasi untuk sesi ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('app.sesi-supervisi.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Kembali</a>
</div>

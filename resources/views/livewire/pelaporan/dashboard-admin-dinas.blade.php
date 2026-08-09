<div class="mx-auto max-w-4xl space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Dashboard Admin Dinas</h1>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs uppercase text-slate-500">Total Sekolah</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $total_sekolah }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs uppercase text-slate-500">Total Sesi</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $total_sesi }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs uppercase text-slate-500">Sesi Selesai</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $sesi_selesai }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs uppercase text-slate-500">Rata-rata Skor</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $rata_rata_skor_kabupaten }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs uppercase text-slate-500">RTL Tertunda</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $rtl_tertunda }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Sekolah</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Total Sesi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">Rata-rata Skor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($skor_per_sekolah as $item)
                    <tr wire:key="sekolah-{{ $item['sekolah_id'] }}">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $item['nama_sekolah'] }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $item['total_sesi'] }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $item['rata_rata_skor'] }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('app.laporan.sekolah', $item['sekolah_id']) }}" class="text-slate-600 hover:text-slate-900">Lihat Laporan</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada sekolah terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

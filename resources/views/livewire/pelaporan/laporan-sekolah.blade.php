<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Laporan Sekolah</h1>
        <p class="text-sm text-slate-500">{{ $nama_sekolah }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
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
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $rata_rata_skor }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs uppercase text-slate-500">RTL Tuntas</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $rtl_tuntas_persen }}%</p>
        </div>
    </div>

    <div class="rounded-xl bg-white p-4 shadow">
        <h2 class="mb-4 text-sm font-medium text-slate-600">Tren Skor</h2>
        @if (empty($tren_skor))
            <p class="py-6 text-center text-sm text-slate-500">Belum ada sesi terobservasi untuk sekolah ini.</p>
        @else
            <div
                x-data="{
                    trenSkor: @js($tren_skor),
                    chart: null,
                    init() {
                        this.chart = new Chart(this.$refs.canvas, {
                            type: 'line',
                            data: {
                                labels: this.trenSkor.map((t) => t.tanggal),
                                datasets: [{
                                    label: 'Skor',
                                    data: this.trenSkor.map((t) => t.skor),
                                    borderColor: '#1e293b',
                                    backgroundColor: '#1e293b',
                                    tension: 0.2,
                                }],
                            },
                            options: {
                                scales: { y: { min: 0, max: 100 } },
                                plugins: { legend: { display: false } },
                            },
                        });
                    },
                }"
                wire:ignore
            >
                <canvas x-ref="canvas" height="80"></canvas>
            </div>
        @endif
    </div>

    @can('laporan.viewDashboard')
        <a href="{{ route('app.laporan.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900">Kembali ke Dashboard</a>
    @else
        <a href="{{ route('beranda') }}" class="text-sm text-slate-600 hover:text-slate-900">Kembali</a>
    @endcan
</div>

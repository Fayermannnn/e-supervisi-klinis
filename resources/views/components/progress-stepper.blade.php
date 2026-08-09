@props(['status'])

@php
    $urutan = ['draft', 'dijadwalkan', 'pra_observasi', 'observasi', 'dianalisis', 'umpan_balik', 'rtl', 'selesai'];
    $label = [
        'draft' => 'Draft',
        'dijadwalkan' => 'Dijadwalkan',
        'pra_observasi' => 'Pra-Observasi',
        'observasi' => 'Observasi',
        'dianalisis' => 'Dianalisis',
        'umpan_balik' => 'Umpan Balik',
        'rtl' => 'RTL',
        'selesai' => 'Selesai',
    ];
    $posisiSaatIni = array_search($status, $urutan, true);
@endphp

<div>
    <div class="flex items-center overflow-x-auto pb-1">
        @foreach ($urutan as $i => $key)
            <div class="flex shrink-0 items-center">
                <div
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold
                        {{ $i < $posisiSaatIni ? 'bg-green-600 text-white' : ($i === $posisiSaatIni ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-500') }}">
                    {{ $i + 1 }}
                </div>
                @unless ($loop->last)
                    <div class="h-0.5 w-4 {{ $i < $posisiSaatIni ? 'bg-green-600' : 'bg-slate-200' }}"></div>
                @endunless
            </div>
        @endforeach
    </div>
    <p class="mt-1 text-xs font-medium text-slate-500">{{ $label[$status] ?? $status }}</p>
</div>

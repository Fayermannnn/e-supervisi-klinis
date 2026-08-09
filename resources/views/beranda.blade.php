@component('layouts.app', ['title' => __('Beranda')])
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-8 text-center shadow">
            <h1 class="mb-2 text-xl font-semibold text-slate-800">Selamat datang, {{ auth()->user()->nama }}</h1>
            <p class="text-sm text-slate-500">Dashboard per peran menyusul di Sprint 9.</p>
        </div>

        @can('pengembangan.manage')
            @if (auth()->user()->hasRole('guru'))
                @livewire('pengembangan-profesional.widget-rekomendasi-guru')
            @endif
        @endcan
    </div>
@endcomponent

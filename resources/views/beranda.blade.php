@component('layouts.centered', ['title' => __('Beranda')])
    <div class="w-full max-w-md rounded-xl bg-white p-8 text-center shadow">
        <h1 class="mb-2 text-xl font-semibold text-slate-800">Selamat datang, {{ auth()->user()->nama }}</h1>
        <p class="mb-6 text-sm text-slate-500">Dashboard per peran menyusul di Sprint 9.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Keluar
            </button>
        </form>
    </div>
@endcomponent

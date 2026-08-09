<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <a href="{{ route('beranda') }}" class="text-lg font-semibold text-slate-800">{{ config('app.name') }}</a>
            <nav class="flex items-center gap-6 text-sm text-slate-600">
                @can('sekolah.manage')
                    <a href="{{ route('app.sekolah.index') }}" class="hover:text-slate-900">Sekolah</a>
                @endcan
                @can('pengguna.manage')
                    <a href="{{ route('app.pengguna.index') }}" class="hover:text-slate-900">Pengguna</a>
                @endcan
                @can('sesi-supervisi.manage')
                    <a href="{{ route('app.sesi-supervisi.index') }}" class="hover:text-slate-900">Sesi Supervisi</a>
                @endcan
                @can('instrumen.manage')
                    <a href="{{ route('app.instrumen.index') }}" class="hover:text-slate-900">Instrumen</a>
                @endcan
                @can('notifikasi.manage')
                    @livewire('notifikasi.badge')
                @endcan
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-white hover:bg-slate-700">
                        Keluar
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>

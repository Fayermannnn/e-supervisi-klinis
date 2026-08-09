<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-xl font-semibold text-slate-800">Form Umpan Balik</h1>
    <p class="text-sm text-slate-500">{{ $sesiSupervisi->guru->nama }} - {{ $sesiSupervisi->tanggal->format('d/m/Y') }}</p>

    <form wire:submit="simpan" class="space-y-4 rounded-xl bg-white p-8 shadow">
        @if ($sesiSupervisi->pendekatan_disarankan)
            <div>
                <label for="pendekatan_dipakai" class="block text-sm font-medium text-slate-700">
                    Pendekatan yang Dipakai
                    <span class="font-normal text-slate-400">(disarankan: {{ $sesiSupervisi->pendekatan_disarankan }})</span>
                </label>
                <select id="pendekatan_dipakai" wire:model.live="pendekatan_dipakai"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    <option value="directive_control">Directive Control</option>
                    <option value="directive_informational">Directive Informational</option>
                    <option value="collaborative">Collaborative</option>
                    <option value="nondirective">Nondirective</option>
                </select>
                <p class="mt-1 text-xs text-slate-400">Boleh berbeda dari saran sistem sesuai penilaian profesional Anda.</p>
            </div>
        @endif

        <div>
            <label for="kekuatan" class="block text-sm font-medium text-slate-700">{{ $this->label['kekuatan'] }}</label>
            <textarea id="kekuatan" wire:model="kekuatan" rows="3"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
            @error('kekuatan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="area_pengembangan" class="block text-sm font-medium text-slate-700">{{ $this->label['area_pengembangan'] }}</label>
            <textarea id="area_pengembangan" wire:model="area_pengembangan" rows="3"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
            @error('area_pengembangan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="rekomendasi" class="block text-sm font-medium text-slate-700">{{ $this->label['rekomendasi'] }}</label>
            <textarea id="rekomendasi" wire:model="rekomendasi" rows="3"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
            @error('rekomendasi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('app.sesi-supervisi.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                Simpan
            </button>
        </div>
    </form>
</div>

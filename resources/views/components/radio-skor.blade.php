@props(['model', 'min' => 1, 'max' => 4, 'label' => null])

<div>
    @if ($label)
        <span class="mb-2 block text-sm font-medium text-slate-700">{{ $label }}</span>
    @endif
    <div class="grid grid-cols-4 gap-2">
        @for ($i = $min; $i <= $max; $i++)
            <label class="block">
                <input type="radio" wire:model="{{ $model }}" value="{{ $i }}" class="peer sr-only">
                <div
                    class="flex h-14 items-center justify-center rounded-lg border-2 border-slate-200 bg-white text-xl font-semibold text-slate-700 select-none peer-checked:border-slate-800 peer-checked:bg-slate-800 peer-checked:text-white">
                    {{ $i }}
                </div>
            </label>
        @endfor
    </div>
</div>

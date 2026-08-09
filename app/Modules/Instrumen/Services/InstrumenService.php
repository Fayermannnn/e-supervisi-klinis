<?php

namespace App\Modules\Instrumen\Services;

use App\Exceptions\Br06InstrumenTerkunciException;
use App\Exceptions\ButirObservasiNotFoundException;
use App\Exceptions\InstrumenObservasiNotFoundException;
use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstrumenService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return InstrumenObservasi::query()->withCount('butir')->latest('versi')->paginate($perPage);
    }

    /** @throws InstrumenObservasiNotFoundException */
    public function find(string $id): InstrumenObservasi
    {
        $instrumen = InstrumenObservasi::with('butir')->find($id);

        if (! $instrumen) {
            throw new InstrumenObservasiNotFoundException;
        }

        return $instrumen;
    }

    /** @param  array<string, mixed>  $data */
    public function create(array $data): InstrumenObservasi
    {
        return InstrumenObservasi::create(array_merge([
            'skor_min' => 1,
            'skor_maks' => 4,
            'terkunci' => false,
        ], $data));
    }

    /**
     * BR-06: Instrumen aktif (terkunci) tidak bisa diedit.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Br06InstrumenTerkunciException
     * @throws InstrumenObservasiNotFoundException
     */
    public function update(string $id, array $data): InstrumenObservasi
    {
        $instrumen = $this->find($id);

        if ($instrumen->terkunci) {
            throw new Br06InstrumenTerkunciException;
        }

        $instrumen->update($data);

        return $instrumen;
    }

    /**
     * Mengunci versi instrumen sehingga tidak bisa diedit lagi (BR-06).
     * Versi baru dibuat lewat create() terpisah, versi lama tidak pernah
     * disentuh (AC: "Versi baru tanpa ubah versi lama").
     *
     * @throws InstrumenObservasiNotFoundException
     */
    public function aktifkan(string $id): InstrumenObservasi
    {
        $instrumen = $this->find($id);
        $instrumen->update(['terkunci' => true]);

        return $instrumen;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Br06InstrumenTerkunciException
     * @throws InstrumenObservasiNotFoundException
     */
    public function tambahButir(string $instrumenId, array $data): ButirObservasi
    {
        $instrumen = $this->find($instrumenId);

        if ($instrumen->terkunci) {
            throw new Br06InstrumenTerkunciException;
        }

        return $instrumen->butir()->create($data);
    }

    /** @throws ButirObservasiNotFoundException */
    public function temukanButir(string $butirId): ButirObservasi
    {
        $butir = ButirObservasi::with('instrumen')->find($butirId);

        if (! $butir) {
            throw new ButirObservasiNotFoundException;
        }

        return $butir;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Br06InstrumenTerkunciException
     * @throws ButirObservasiNotFoundException
     */
    public function updateButir(string $butirId, array $data): ButirObservasi
    {
        $butir = $this->temukanButir($butirId);

        if ($butir->instrumen->terkunci) {
            throw new Br06InstrumenTerkunciException;
        }

        $butir->update($data);

        return $butir;
    }

    /**
     * @throws Br06InstrumenTerkunciException
     * @throws ButirObservasiNotFoundException
     */
    public function hapusButir(string $butirId): void
    {
        $butir = $this->temukanButir($butirId);

        if ($butir->instrumen->terkunci) {
            throw new Br06InstrumenTerkunciException;
        }

        $butir->delete();
    }
}

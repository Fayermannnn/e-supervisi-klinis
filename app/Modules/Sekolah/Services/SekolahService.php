<?php

namespace App\Modules\Sekolah\Services;

use App\Exceptions\SekolahNotFoundException;
use App\Models\Sekolah;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SekolahService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Sekolah::query()->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Sekolah
    {
        return Sekolah::create(array_merge(['status_aktif' => true], $data));
    }

    /**
     * @throws SekolahNotFoundException
     */
    public function find(string $id): Sekolah
    {
        $sekolah = Sekolah::find($id);

        if (! $sekolah) {
            throw new SekolahNotFoundException;
        }

        return $sekolah;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws SekolahNotFoundException
     */
    public function update(string $id, array $data): Sekolah
    {
        $sekolah = $this->find($id);

        $sekolah->update($data);

        return $sekolah;
    }

    /**
     * @throws SekolahNotFoundException
     */
    public function delete(string $id): void
    {
        $sekolah = $this->find($id);

        $sekolah->delete();
    }
}

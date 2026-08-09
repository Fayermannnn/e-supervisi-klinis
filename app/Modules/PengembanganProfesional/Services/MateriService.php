<?php

namespace App\Modules\PengembanganProfesional\Services;

use App\Exceptions\MateriPengembanganNotFoundException;
use App\Models\MateriPengembangan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MateriService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return MateriPengembangan::query()->latest()->paginate($perPage);
    }

    /** @throws MateriPengembanganNotFoundException */
    public function find(string $id): MateriPengembangan
    {
        $materi = MateriPengembangan::find($id);

        if (! $materi) {
            throw new MateriPengembanganNotFoundException;
        }

        return $materi;
    }

    /** @param  array<string, mixed>  $data */
    public function create(array $data): MateriPengembangan
    {
        return MateriPengembangan::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws MateriPengembanganNotFoundException
     */
    public function update(string $id, array $data): MateriPengembangan
    {
        $materi = $this->find($id);
        $materi->update($data);

        return $materi;
    }

    /** @throws MateriPengembanganNotFoundException */
    public function delete(string $id): void
    {
        $this->find($id)->delete();
    }
}

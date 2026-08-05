<?php

namespace App\Modules\Pengguna\Services;

use App\Exceptions\PenggunaNotFoundException;
use App\Models\Pengguna;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class PenggunaService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Pengguna::query()->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Pengguna
    {
        $data['password'] = Hash::make($data['password']);

        return Pengguna::create(array_merge(['status_aktif' => true], $data));
    }

    /**
     * @throws PenggunaNotFoundException
     */
    public function find(string $id): Pengguna
    {
        $pengguna = Pengguna::find($id);

        if (! $pengguna) {
            throw new PenggunaNotFoundException;
        }

        return $pengguna;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws PenggunaNotFoundException
     */
    public function update(string $id, array $data): Pengguna
    {
        $pengguna = $this->find($id);

        if (array_key_exists('password', $data)) {
            $data['password'] = filled($data['password']) ? Hash::make($data['password']) : $pengguna->password;
        }

        $pengguna->update($data);

        return $pengguna;
    }

    /**
     * @throws PenggunaNotFoundException
     */
    public function delete(string $id): void
    {
        $pengguna = $this->find($id);

        $pengguna->delete();
    }
}

<?php

namespace App\Modules\Pengguna\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna as PenggunaModel;
use App\Modules\Pengguna\Http\Requests\StorePenggunaRequest;
use App\Modules\Pengguna\Http\Requests\UpdatePenggunaRequest;
use App\Modules\Pengguna\Services\PenggunaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    public function __construct(private readonly PenggunaService $penggunaService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PenggunaModel::class);

        /** @var PenggunaModel $actor */
        $actor = $request->user();
        $sekolahId = $actor->hasRole('kepala_sekolah') ? $actor->sekolah_id : null;

        $perPage = (int) $request->integer('per_page', 15);

        $pengguna = $this->penggunaService->list($perPage, $sekolahId);

        return ApiResponse::success($pengguna->items(), [
            'current_page' => $pengguna->currentPage(),
            'per_page' => $pengguna->perPage(),
            'total' => $pengguna->total(),
            'last_page' => $pengguna->lastPage(),
        ]);
    }

    public function store(StorePenggunaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->authorize('create', [PenggunaModel::class, $data['sekolah_id'] ?? null]);

        $pengguna = $this->penggunaService->create($data);

        return ApiResponse::success($pengguna, [], 201);
    }

    public function show(string $pengguna): JsonResponse
    {
        $model = $this->penggunaService->find($pengguna);

        $this->authorize('view', $model);

        return ApiResponse::success($model);
    }

    public function update(UpdatePenggunaRequest $request, string $pengguna): JsonResponse
    {
        $model = $this->penggunaService->find($pengguna);

        $this->authorize('update', $model);

        return ApiResponse::success($this->penggunaService->update($pengguna, $request->validated()));
    }

    public function destroy(string $pengguna): JsonResponse
    {
        $model = $this->penggunaService->find($pengguna);

        $this->authorize('delete', $model);

        $this->penggunaService->delete($pengguna);

        return ApiResponse::success(['message' => 'Pengguna berhasil dihapus.']);
    }
}

<?php

namespace App\Modules\Pengguna\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $perPage = (int) $request->integer('per_page', 15);

        $pengguna = $this->penggunaService->list($perPage);

        return ApiResponse::success($pengguna->items(), [
            'current_page' => $pengguna->currentPage(),
            'per_page' => $pengguna->perPage(),
            'total' => $pengguna->total(),
            'last_page' => $pengguna->lastPage(),
        ]);
    }

    public function store(StorePenggunaRequest $request): JsonResponse
    {
        $pengguna = $this->penggunaService->create($request->validated());

        return ApiResponse::success($pengguna, [], 201);
    }

    public function show(string $pengguna): JsonResponse
    {
        return ApiResponse::success($this->penggunaService->find($pengguna));
    }

    public function update(UpdatePenggunaRequest $request, string $pengguna): JsonResponse
    {
        return ApiResponse::success($this->penggunaService->update($pengguna, $request->validated()));
    }

    public function destroy(string $pengguna): JsonResponse
    {
        $this->penggunaService->delete($pengguna);

        return ApiResponse::success(['message' => 'Pengguna berhasil dihapus.']);
    }
}

<?php

namespace App\Modules\Sekolah\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sekolah\Http\Requests\StoreSekolahRequest;
use App\Modules\Sekolah\Http\Requests\UpdateSekolahRequest;
use App\Modules\Sekolah\Services\SekolahService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function __construct(private readonly SekolahService $sekolahService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        $sekolah = $this->sekolahService->list($perPage);

        return ApiResponse::success($sekolah->items(), [
            'current_page' => $sekolah->currentPage(),
            'per_page' => $sekolah->perPage(),
            'total' => $sekolah->total(),
            'last_page' => $sekolah->lastPage(),
        ]);
    }

    public function store(StoreSekolahRequest $request): JsonResponse
    {
        $sekolah = $this->sekolahService->create($request->validated());

        return ApiResponse::success($sekolah, [], 201);
    }

    public function show(string $sekolah): JsonResponse
    {
        return ApiResponse::success($this->sekolahService->find($sekolah));
    }

    public function update(UpdateSekolahRequest $request, string $sekolah): JsonResponse
    {
        return ApiResponse::success($this->sekolahService->update($sekolah, $request->validated()));
    }

    public function destroy(string $sekolah): JsonResponse
    {
        $this->sekolahService->delete($sekolah);

        return ApiResponse::success(['message' => 'Sekolah berhasil dihapus.']);
    }
}

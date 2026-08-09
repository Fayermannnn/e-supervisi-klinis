<?php

namespace App\Modules\PengembanganProfesional\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MateriPengembangan;
use App\Modules\PengembanganProfesional\Http\Requests\StoreMateriRequest;
use App\Modules\PengembanganProfesional\Http\Requests\UpdateMateriRequest;
use App\Modules\PengembanganProfesional\Services\MateriService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    public function __construct(private readonly MateriService $materiService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MateriPengembangan::class);
        $perPage = (int) $request->integer('per_page', 15);
        $materi = $this->materiService->list($perPage);

        return ApiResponse::success($materi->items(), [
            'current_page' => $materi->currentPage(),
            'per_page' => $materi->perPage(),
            'total' => $materi->total(),
            'last_page' => $materi->lastPage(),
        ]);
    }

    public function store(StoreMateriRequest $request): JsonResponse
    {
        $this->authorize('create', MateriPengembangan::class);
        $materi = $this->materiService->create($request->validated());

        return ApiResponse::success($materi, [], 201);
    }

    public function show(string $materiPengembangan): JsonResponse
    {
        $this->authorize('view', MateriPengembangan::class);
        $materi = $this->materiService->find($materiPengembangan);

        return ApiResponse::success($materi);
    }

    public function update(UpdateMateriRequest $request, string $materiPengembangan): JsonResponse
    {
        $this->authorize('update', MateriPengembangan::class);
        $materi = $this->materiService->update($materiPengembangan, $request->validated());

        return ApiResponse::success($materi);
    }

    public function destroy(string $materiPengembangan): JsonResponse
    {
        $this->authorize('delete', MateriPengembangan::class);
        $this->materiService->delete($materiPengembangan);

        return ApiResponse::success(['message' => 'Materi pengembangan berhasil dihapus.']);
    }
}

<?php

namespace App\Modules\Instrumen\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InstrumenObservasi;
use App\Modules\Instrumen\Http\Requests\StoreButirRequest;
use App\Modules\Instrumen\Http\Requests\StoreInstrumenRequest;
use App\Modules\Instrumen\Http\Requests\UpdateButirRequest;
use App\Modules\Instrumen\Http\Requests\UpdateInstrumenRequest;
use App\Modules\Instrumen\Services\InstrumenService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstrumenController extends Controller
{
    public function __construct(private readonly InstrumenService $instrumenService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InstrumenObservasi::class);
        $perPage = (int) $request->integer('per_page', 15);
        $instrumen = $this->instrumenService->list($perPage);

        return ApiResponse::success($instrumen->items(), [
            'current_page' => $instrumen->currentPage(),
            'per_page' => $instrumen->perPage(),
            'total' => $instrumen->total(),
            'last_page' => $instrumen->lastPage(),
        ]);
    }

    public function store(StoreInstrumenRequest $request): JsonResponse
    {
        $this->authorize('create', InstrumenObservasi::class);
        $instrumen = $this->instrumenService->create($request->validated());

        return ApiResponse::success($instrumen, [], 201);
    }

    public function show(string $instrumenObservasi): JsonResponse
    {
        $this->authorize('view', InstrumenObservasi::class);
        $instrumen = $this->instrumenService->find($instrumenObservasi);

        return ApiResponse::success($instrumen);
    }

    public function update(UpdateInstrumenRequest $request, string $instrumenObservasi): JsonResponse
    {
        $this->authorize('update', InstrumenObservasi::class);
        $instrumen = $this->instrumenService->update($instrumenObservasi, $request->validated());

        return ApiResponse::success($instrumen);
    }

    public function aktifkan(string $instrumenObservasi): JsonResponse
    {
        $this->authorize('update', InstrumenObservasi::class);
        $instrumen = $this->instrumenService->aktifkan($instrumenObservasi);

        return ApiResponse::success($instrumen);
    }

    public function tambahButir(StoreButirRequest $request, string $instrumenObservasi): JsonResponse
    {
        $this->authorize('update', InstrumenObservasi::class);
        $butir = $this->instrumenService->tambahButir($instrumenObservasi, $request->validated());

        return ApiResponse::success($butir, [], 201);
    }

    public function updateButir(UpdateButirRequest $request, string $butirObservasi): JsonResponse
    {
        $this->authorize('update', InstrumenObservasi::class);
        $butir = $this->instrumenService->updateButir($butirObservasi, $request->validated());

        return ApiResponse::success($butir);
    }

    public function hapusButir(string $butirObservasi): JsonResponse
    {
        $this->authorize('delete', InstrumenObservasi::class);
        $this->instrumenService->hapusButir($butirObservasi);

        return ApiResponse::success(['message' => 'Butir observasi berhasil dihapus.']);
    }
}

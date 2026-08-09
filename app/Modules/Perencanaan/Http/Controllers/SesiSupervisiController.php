<?php

namespace App\Modules\Perencanaan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\SesiSupervisi;
use App\Modules\Perencanaan\Http\Requests\PraObservasiRequest;
use App\Modules\Perencanaan\Http\Requests\StoreSesiSupervisiRequest;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesiSupervisiController extends Controller
{
    public function __construct(private readonly SesiSupervisiService $sesiSupervisiService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SesiSupervisi::class);
        $perPage = (int) $request->integer('per_page', 15);

        /** @var Pengguna $actor */
        $actor = $request->user();

        $sesi = $this->sesiSupervisiService->list(
            perPage: $perPage,
            guruId: $actor->hasRole('guru') ? $actor->id : null,
            supervisorId: $actor->hasRole('supervisor') ? $actor->id : null,
            sekolahId: $actor->hasRole('kepala_sekolah') ? $actor->sekolah_id : null,
        );

        return ApiResponse::success($sesi->items(), [
            'current_page' => $sesi->currentPage(),
            'per_page' => $sesi->perPage(),
            'total' => $sesi->total(),
            'last_page' => $sesi->lastPage(),
        ]);
    }

    public function store(StoreSesiSupervisiRequest $request): JsonResponse
    {
        $this->authorize('create', SesiSupervisi::class);
        $sesi = $this->sesiSupervisiService->buatJadwal($request->validated());

        return ApiResponse::success($sesi, [], 201);
    }

    public function show(string $sesiSupervisi): JsonResponse
    {
        $model = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('view', $model);

        return ApiResponse::success($model);
    }

    public function praObservasi(PraObservasiRequest $request, string $sesiSupervisi): JsonResponse
    {
        $model = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('update', $model);

        $sesi = $this->sesiSupervisiService->isiPraObservasi($sesiSupervisi, $request->validated());

        return ApiResponse::success($sesi);
    }
}

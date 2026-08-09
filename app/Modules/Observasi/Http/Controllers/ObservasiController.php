<?php

namespace App\Modules\Observasi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Observasi\Http\Requests\SimpanHasilObservasiRequest;
use App\Modules\Observasi\Services\HasilObservasiService;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ObservasiController extends Controller
{
    public function __construct(
        private readonly HasilObservasiService $hasilObservasiService,
        private readonly SesiSupervisiService $sesiSupervisiService,
    ) {}

    public function simpan(SimpanHasilObservasiRequest $request, string $sesiSupervisi): JsonResponse
    {
        $sesi = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('update', $sesi);

        $sesi = $this->hasilObservasiService->simpanHasil($sesiSupervisi, $request->validated());

        return ApiResponse::success($sesi);
    }
}

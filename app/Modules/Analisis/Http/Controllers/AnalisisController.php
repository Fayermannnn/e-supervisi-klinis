<?php

namespace App\Modules\Analisis\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Analisis\Services\SkorCalculator;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AnalisisController extends Controller
{
    public function __construct(
        private readonly SkorCalculator $skorCalculator,
        private readonly SesiSupervisiService $sesiSupervisiService,
    ) {}

    public function skor(string $sesiSupervisi): JsonResponse
    {
        $sesi = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('view', $sesi);

        return ApiResponse::success($this->skorCalculator->hitung($sesi));
    }
}

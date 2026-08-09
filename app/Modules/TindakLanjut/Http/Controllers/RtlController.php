<?php

namespace App\Modules\TindakLanjut\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RencanaTindakLanjut;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use App\Modules\TindakLanjut\Http\Requests\IsiRtlRequest;
use App\Modules\TindakLanjut\Services\RtlService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class RtlController extends Controller
{
    public function __construct(
        private readonly RtlService $rtlService,
        private readonly SesiSupervisiService $sesiSupervisiService,
    ) {}

    public function store(IsiRtlRequest $request, string $sesiSupervisi): JsonResponse
    {
        $sesi = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('create', [RencanaTindakLanjut::class, $sesi]);

        $rtl = $this->rtlService->isiRtl($sesiSupervisi, $request->validated());

        return ApiResponse::success($rtl, [], 201);
    }

    public function update(IsiRtlRequest $request, string $sesiSupervisi): JsonResponse
    {
        $rtl = $this->rtlService->find($sesiSupervisi);
        $this->authorize('update', $rtl);

        $rtl = $this->rtlService->isiRtl($sesiSupervisi, $request->validated());

        return ApiResponse::success($rtl);
    }

    public function show(string $sesiSupervisi): JsonResponse
    {
        $rtl = $this->rtlService->find($sesiSupervisi);
        $this->authorize('view', $rtl);

        return ApiResponse::success($rtl);
    }

    public function selesaikan(string $sesiSupervisi): JsonResponse
    {
        $sesi = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('update', $sesi);

        $sesi = $this->rtlService->tandaiSelesai($sesiSupervisi);

        return ApiResponse::success($sesi);
    }
}

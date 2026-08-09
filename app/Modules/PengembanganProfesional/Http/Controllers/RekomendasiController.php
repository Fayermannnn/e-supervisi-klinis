<?php

namespace App\Modules\PengembanganProfesional\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\RekomendasiPengembangan;
use App\Modules\PengembanganProfesional\Http\Requests\RekomendasiManualRequest;
use App\Modules\PengembanganProfesional\Http\Requests\UbahStatusRekomendasiRequest;
use App\Modules\PengembanganProfesional\Services\RekomendasiService;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RekomendasiController extends Controller
{
    public function __construct(
        private readonly RekomendasiService $rekomendasiService,
        private readonly SesiSupervisiService $sesiSupervisiService,
    ) {}

    public function rekomendasikanManual(RekomendasiManualRequest $request, string $sesiSupervisi): JsonResponse
    {
        $sesi = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('create', [RekomendasiPengembangan::class, $sesi]);

        $rekomendasi = $this->rekomendasiService->rekomendasikanManual([
            'pengguna_id' => $sesi->guru_id,
            'sesi_id' => $sesi->id,
            'materi_id' => $request->validated('materi_id'),
        ]);

        return ApiResponse::success($rekomendasi, [], 201);
    }

    public function untukPengguna(Request $request, string $pengguna): JsonResponse
    {
        /** @var Pengguna $actor */
        $actor = $request->user();

        abort_unless(
            $actor->id === $pengguna || $actor->hasRole('admin_dinas') || $actor->hasRole('kepala_sekolah'),
            403,
        );

        $perPage = (int) $request->integer('per_page', 15);
        $rekomendasi = $this->rekomendasiService->untukPengguna($pengguna, $perPage);

        return ApiResponse::success($rekomendasi->items(), [
            'current_page' => $rekomendasi->currentPage(),
            'per_page' => $rekomendasi->perPage(),
            'total' => $rekomendasi->total(),
            'last_page' => $rekomendasi->lastPage(),
        ]);
    }

    public function ubahStatus(UbahStatusRekomendasiRequest $request, string $rekomendasi): JsonResponse
    {
        $rekomendasiModel = $this->rekomendasiService->find($rekomendasi);
        $this->authorize('ubahStatus', $rekomendasiModel);

        $updated = $this->rekomendasiService->ubahStatus($rekomendasi, $request->validated('status'));

        return ApiResponse::success($updated);
    }
}

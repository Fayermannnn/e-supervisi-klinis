<?php

namespace App\Modules\UmpanBalik\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\UmpanBalik;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use App\Modules\UmpanBalik\Http\Requests\IsiRefleksiRequest;
use App\Modules\UmpanBalik\Http\Requests\StoreUmpanBalikRequest;
use App\Modules\UmpanBalik\Http\Requests\UbahPendekatanRequest;
use App\Modules\UmpanBalik\Services\UmpanBalikService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UmpanBalikController extends Controller
{
    public function __construct(
        private readonly UmpanBalikService $umpanBalikService,
        private readonly SesiSupervisiService $sesiSupervisiService,
    ) {}

    public function store(StoreUmpanBalikRequest $request, string $sesiSupervisi): JsonResponse
    {
        $sesi = $this->sesiSupervisiService->find($sesiSupervisi);
        $this->authorize('create', [UmpanBalik::class, $sesi]);

        $umpanBalik = $this->umpanBalikService->isiUmpanBalik($sesiSupervisi, $request->validated());

        return ApiResponse::success($this->tampilkan($umpanBalik, redacted: false), [], 201);
    }

    public function show(Request $request, string $sesiSupervisi): JsonResponse
    {
        $umpanBalik = $this->umpanBalikService->find($sesiSupervisi);
        $this->authorize('view', $umpanBalik);

        /** @var Pengguna $actor */
        $actor = $request->user();
        $redacted = $actor->hasRole('admin_dinas');

        return ApiResponse::success($this->tampilkan($umpanBalik, $redacted));
    }

    public function ubahPendekatan(UbahPendekatanRequest $request, string $sesiSupervisi): JsonResponse
    {
        $umpanBalik = $this->umpanBalikService->find($sesiSupervisi);
        $this->authorize('update', $umpanBalik);

        $umpanBalik = $this->umpanBalikService->ubahPendekatan($sesiSupervisi, $request->validated()['pendekatan_dipakai']);

        return ApiResponse::success($this->tampilkan($umpanBalik, redacted: false));
    }

    public function isiRefleksi(IsiRefleksiRequest $request, string $sesiSupervisi): JsonResponse
    {
        $umpanBalik = $this->umpanBalikService->find($sesiSupervisi);
        $this->authorize('refleksi', $umpanBalik);

        $umpanBalik = $this->umpanBalikService->isiRefleksi($sesiSupervisi, $request->validated()['refleksi_guru']);

        return ApiResponse::success($this->tampilkan($umpanBalik, redacted: false));
    }

    /**
     * BR-08 (Sprint 7): field sensitif diredaksi eksplisit untuk Admin
     * Dinas ("redacted": true), bukan dihilangkan diam-diam.
     *
     * @return array<string, mixed>
     */
    private function tampilkan(UmpanBalik $umpanBalik, bool $redacted): array
    {
        return [
            'id' => $umpanBalik->id,
            'sesi_id' => $umpanBalik->sesi_id,
            'kekuatan' => $redacted ? null : $umpanBalik->kekuatan,
            'area_pengembangan' => $redacted ? null : $umpanBalik->area_pengembangan,
            'rekomendasi' => $redacted ? null : $umpanBalik->rekomendasi,
            'refleksi_guru' => $redacted ? null : $umpanBalik->refleksi_guru,
            'pendekatan_dipakai' => $umpanBalik->pendekatan_dipakai,
            'terlambat' => $umpanBalik->terlambat,
            'tanggal_diisi' => $umpanBalik->tanggal_diisi,
            'redacted' => $redacted,
        ];
    }
}

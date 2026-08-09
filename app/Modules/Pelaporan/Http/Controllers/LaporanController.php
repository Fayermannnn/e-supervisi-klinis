<?php

namespace App\Modules\Pelaporan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Sekolah;
use App\Modules\Pelaporan\Http\Requests\DrillDownIndividualRequest;
use App\Modules\Pelaporan\Queries\LaporanAgregatQuery;
use App\Modules\Pelaporan\Services\PelaporanService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LaporanController extends Controller
{
    public function __construct(
        private readonly LaporanAgregatQuery $laporanAgregatQuery,
        private readonly PelaporanService $pelaporanService,
    ) {}

    public function sekolah(Sekolah $sekolah): JsonResponse
    {
        $this->authorize('viewLaporan', $sekolah);

        return ApiResponse::success($this->laporanAgregatQuery->rekapSekolah($sekolah));
    }

    public function dashboard(): JsonResponse
    {
        $this->authorize('laporan.viewDashboard');

        return ApiResponse::success($this->laporanAgregatQuery->dashboardAgregat());
    }

    /**
     * BR-08 drill-down: POST /laporan/individual/{sesi_id}. Justifikasi
     * wajib (DrillDownIndividualRequest) dan tercatat sebagai audit_log
     * (PelaporanService::drillDownIndividual()).
     */
    public function individual(DrillDownIndividualRequest $request, string $sesiSupervisi): JsonResponse
    {
        $this->authorize('laporan.drillDownIndividual');

        /** @var Pengguna $admin */
        $admin = $request->user();

        $hasil = $this->pelaporanService->drillDownIndividual(
            admin: $admin,
            sesiId: $sesiSupervisi,
            justifikasi: $request->validated()['justifikasi'],
            ipAddress: $request->ip(),
            correlationId: $request->attributes->get('correlation_id'),
        );

        return ApiResponse::success($hasil, [], 201);
    }
}

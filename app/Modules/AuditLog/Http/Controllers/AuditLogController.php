<?php

namespace App\Modules\AuditLog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Modules\AuditLog\Http\Requests\ListAuditLogRequest;
use App\Modules\AuditLog\Services\AuditLogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(ListAuditLogRequest $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $data = $request->validated();

        $logs = $this->auditLogService->list(
            filters: ['aksi' => $data['aksi'] ?? null, 'dari' => $data['dari'] ?? null, 'sampai' => $data['sampai'] ?? null],
            encodedCursor: $data['cursor'] ?? null,
        );

        return ApiResponse::success($logs->items(), [
            'next_cursor' => $logs->nextCursor()?->encode(),
            'prev_cursor' => $logs->previousCursor()?->encode(),
        ]);
    }
}

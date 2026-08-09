<?php

namespace App\Livewire\AuditLog;

use App\Models\AuditLog;
use App\Modules\AuditLog\Services\AuditLogService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Log Audit')]
class LogAudit extends Component
{
    #[Url]
    public string $aksi = '';

    #[Url]
    public ?string $dari = null;

    #[Url]
    public ?string $sampai = null;

    #[Url]
    public ?string $cursor = null;

    public ?string $cursorBerikutnya = null;

    public ?string $cursorSebelumnya = null;

    public function mount(): void
    {
        $this->authorize('viewAny', AuditLog::class);
    }

    public function updatedAksi(): void
    {
        $this->cursor = null;
    }

    public function updatedDari(): void
    {
        $this->cursor = null;
    }

    public function updatedSampai(): void
    {
        $this->cursor = null;
    }

    public function muatBerikutnya(): void
    {
        $this->cursor = $this->cursorBerikutnya;
    }

    public function muatSebelumnya(): void
    {
        $this->cursor = $this->cursorSebelumnya;
    }

    public function render(AuditLogService $auditLogService)
    {
        $logs = $auditLogService->list(
            filters: ['aksi' => $this->aksi ?: null, 'dari' => $this->dari ?: null, 'sampai' => $this->sampai ?: null],
            encodedCursor: $this->cursor,
        );

        $this->cursorBerikutnya = $logs->nextCursor()?->encode();
        $this->cursorSebelumnya = $logs->previousCursor()?->encode();

        return view('livewire.audit-log.log-audit', ['logs' => $logs]);
    }
}

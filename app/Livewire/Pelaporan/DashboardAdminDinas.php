<?php

namespace App\Livewire\Pelaporan;

use App\Modules\Pelaporan\Queries\LaporanAgregatQuery;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Admin Dinas')]
class DashboardAdminDinas extends Component
{
    public function mount(): void
    {
        $this->authorize('laporan.viewDashboard');
    }

    public function render(LaporanAgregatQuery $laporanAgregatQuery)
    {
        return view('livewire.pelaporan.dashboard-admin-dinas', $laporanAgregatQuery->dashboardAgregat());
    }
}

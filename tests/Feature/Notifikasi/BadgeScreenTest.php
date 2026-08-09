<?php

namespace Tests\Feature\Notifikasi;

use App\Livewire\Notifikasi\Badge;
use App\Models\Notifikasi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class BadgeScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_badge_shows_unread_count_for_current_pengguna(): void
    {
        $pengguna = $this->penggunaWithRole('guru');
        $this->actingAs($pengguna, 'web');

        Notifikasi::factory()->count(2)->create(['pengguna_id' => $pengguna->id, 'dibaca' => false]);
        Notifikasi::factory()->create(['pengguna_id' => $pengguna->id, 'dibaca' => true]);

        Livewire::test(Badge::class)
            ->assertViewHas('jumlahBelumDibaca', 2);
    }

    public function test_badge_does_not_count_notifikasi_milik_pengguna_lain(): void
    {
        $pengguna = $this->penggunaWithRole('guru');
        $lainPengguna = $this->penggunaWithRole('supervisor');
        $this->actingAs($pengguna, 'web');

        Notifikasi::factory()->count(4)->create(['pengguna_id' => $lainPengguna->id, 'dibaca' => false]);

        Livewire::test(Badge::class)
            ->assertViewHas('jumlahBelumDibaca', 0);
    }

    public function test_tandai_dibaca_marks_notifikasi_read(): void
    {
        $pengguna = $this->penggunaWithRole('guru');
        $this->actingAs($pengguna, 'web');

        $notifikasi = Notifikasi::factory()->create(['pengguna_id' => $pengguna->id, 'dibaca' => false]);

        Livewire::test(Badge::class)
            ->call('toggle')
            ->call('tandaiDibaca', $notifikasi->id);

        $this->assertDatabaseHas('notifikasi', ['id' => $notifikasi->id, 'dibaca' => true]);
    }

    public function test_tandai_dibaca_menolak_notifikasi_milik_pengguna_lain(): void
    {
        $pengguna = $this->penggunaWithRole('guru');
        $lainPengguna = $this->penggunaWithRole('supervisor');
        $this->actingAs($pengguna, 'web');

        $notifikasi = Notifikasi::factory()->create(['pengguna_id' => $lainPengguna->id, 'dibaca' => false]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Badge::class)->call('tandaiDibaca', $notifikasi->id);
    }
}

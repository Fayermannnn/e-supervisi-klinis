<?php

namespace Tests\Feature\Sekolah;

use App\Livewire\Sekolah\Create;
use App\Livewire\Sekolah\Edit;
use App\Livewire\Sekolah\Index;
use App\Models\Pengguna;
use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SekolahScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekolah_index_requires_authentication(): void
    {
        $this->get('/sekolah')->assertRedirect(route('login'));
    }

    public function test_sekolah_index_renders_and_lists_sekolah(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $sekolah = Sekolah::factory()->create(['nama_sekolah' => 'SDN 1 Sidoarjo']);

        $this->get('/sekolah')
            ->assertOk()
            ->assertSeeLivewire(Index::class)
            ->assertSee('SDN 1 Sidoarjo');
    }

    public function test_sekolah_index_can_delete_sekolah(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $sekolah = Sekolah::factory()->create();

        Livewire::test(Index::class)->call('hapus', $sekolah->id);

        $this->assertSoftDeleted('sekolah', ['id' => $sekolah->id]);
    }

    public function test_sekolah_create_screen_renders(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $this->get('/sekolah/tambah')->assertOk()->assertSeeLivewire(Create::class);
    }

    public function test_sekolah_create_validates_required_fields(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        Livewire::test(Create::class)
            ->set('nama_sekolah', '')
            ->call('simpan')
            ->assertHasErrors(['nama_sekolah']);
    }

    public function test_sekolah_create_stores_new_sekolah_and_redirects(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        Livewire::test(Create::class)
            ->set('nama_sekolah', 'SDN 1 Sidoarjo')
            ->set('npsn', '20501234')
            ->set('alamat', 'Jl. Merdeka No. 1')
            ->call('simpan')
            ->assertRedirect(route('app.sekolah.index'));

        $this->assertDatabaseHas('sekolah', ['npsn' => '20501234']);
    }

    public function test_sekolah_create_rejects_duplicate_npsn(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        Sekolah::factory()->create(['npsn' => '20501234']);

        Livewire::test(Create::class)
            ->set('nama_sekolah', 'SDN 2 Sidoarjo')
            ->set('npsn', '20501234')
            ->call('simpan')
            ->assertHasErrors(['npsn']);
    }

    public function test_sekolah_edit_screen_loads_existing_values(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $sekolah = Sekolah::factory()->create(['nama_sekolah' => 'SDN Lama']);

        Livewire::test(Edit::class, ['sekolah' => $sekolah])
            ->assertSet('nama_sekolah', 'SDN Lama');
    }

    public function test_sekolah_edit_updates_sekolah_and_redirects(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $sekolah = Sekolah::factory()->create(['nama_sekolah' => 'SDN Lama']);

        Livewire::test(Edit::class, ['sekolah' => $sekolah])
            ->set('nama_sekolah', 'SDN Baru')
            ->call('simpan')
            ->assertRedirect(route('app.sekolah.index'));

        $this->assertDatabaseHas('sekolah', ['id' => $sekolah->id, 'nama_sekolah' => 'SDN Baru']);
    }

    public function test_sekolah_edit_allows_keeping_its_own_npsn(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $sekolah = Sekolah::factory()->create(['npsn' => '20501234']);

        Livewire::test(Edit::class, ['sekolah' => $sekolah])
            ->set('nama_sekolah', 'SDN Baru')
            ->call('simpan')
            ->assertHasNoErrors();
    }
}

<?php

namespace Tests\Feature\Instrumen;

use App\Livewire\Instrumen\Create;
use App\Livewire\Instrumen\Index;
use App\Livewire\Instrumen\Kelola;
use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class InstrumenScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_instrumen_index_requires_authentication(): void
    {
        $this->get('/instrumen')->assertRedirect(route('login'));
    }

    public function test_guru_can_view_instrumen_index_but_not_create_link(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        $instrumen = InstrumenObservasi::factory()->create(['nama' => 'Instrumen Kelas Reguler']);

        $this->get('/instrumen')
            ->assertOk()
            ->assertSeeLivewire(Index::class)
            ->assertSee('Instrumen Kelas Reguler')
            ->assertDontSee('Buat Instrumen');
    }

    public function test_guru_cannot_open_create_screen(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        $this->get('/instrumen/buat')->assertForbidden();
    }

    public function test_admin_dinas_can_create_instrumen_via_livewire(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');

        Livewire::test(Create::class)
            ->set('versi', 1)
            ->set('nama', 'Instrumen Uji Coba')
            ->call('simpan')
            ->assertRedirect();

        $this->assertDatabaseHas('instrumen_observasi', ['nama' => 'Instrumen Uji Coba', 'terkunci' => false]);
    }

    public function test_admin_dinas_can_tambah_butir_via_livewire(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');

        $instrumen = InstrumenObservasi::factory()->create();

        Livewire::test(Kelola::class, ['instrumen' => $instrumen])
            ->set('kode', 'A1')
            ->set('dimensi', 'A. Pembukaan Pembelajaran')
            ->set('teks', 'Guru membuka pembelajaran dengan salam dan doa')
            ->set('bobot', 5)
            ->call('tambahButir');

        $this->assertDatabaseHas('butir_observasi', ['instrumen_id' => $instrumen->id, 'kode' => 'A1']);
    }

    public function test_admin_dinas_can_aktifkan_instrumen_via_livewire(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');

        $instrumen = InstrumenObservasi::factory()->create();

        Livewire::test(Kelola::class, ['instrumen' => $instrumen])
            ->call('aktifkan')
            ->assertSet('instrumen.terkunci', true);

        $this->assertDatabaseHas('instrumen_observasi', ['id' => $instrumen->id, 'terkunci' => true]);
    }

    public function test_tambah_butir_form_hidden_once_instrumen_terkunci(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');

        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        Livewire::test(Kelola::class, ['instrumen' => $instrumen])
            ->assertDontSee('Tambah Butir');
    }
}

<?php

namespace Tests\Feature\Pengguna;

use App\Livewire\Pengguna\Create;
use App\Livewire\Pengguna\Edit;
use App\Livewire\Pengguna\Index;
use App\Models\Pengguna;
use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PenggunaScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_index_requires_authentication(): void
    {
        $this->get('/pengguna')->assertRedirect(route('login'));
    }

    public function test_pengguna_index_renders_and_lists_pengguna(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $pengguna = Pengguna::factory()->create(['nama' => 'Budi Santoso']);

        $this->get('/pengguna')
            ->assertOk()
            ->assertSeeLivewire(Index::class)
            ->assertSee('Budi Santoso');
    }

    public function test_pengguna_index_can_delete_pengguna(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $pengguna = Pengguna::factory()->create();

        Livewire::test(Index::class)->call('hapus', $pengguna->id);

        $this->assertSoftDeleted('pengguna', ['id' => $pengguna->id]);
    }

    public function test_pengguna_create_screen_renders(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $this->get('/pengguna/tambah')->assertOk()->assertSeeLivewire(Create::class);
    }

    public function test_pengguna_create_validates_required_fields(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        Livewire::test(Create::class)
            ->set('nama', '')
            ->set('email', '')
            ->set('password', '')
            ->call('simpan')
            ->assertHasErrors(['nama', 'email', 'password']);
    }

    public function test_pengguna_create_stores_new_pengguna_with_hashed_password(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $sekolah = Sekolah::factory()->create();

        Livewire::test(Create::class)
            ->set('sekolah_id', $sekolah->id)
            ->set('nama', 'Budi Santoso')
            ->set('email', 'budi@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('simpan')
            ->assertRedirect(route('app.pengguna.index'));

        $pengguna = Pengguna::where('email', 'budi@sidoarjo.go.id')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $pengguna->password));
        $this->assertSame($sekolah->id, $pengguna->sekolah_id);
    }

    public function test_pengguna_create_rejects_duplicate_email(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        Pengguna::factory()->create(['email' => 'dup@sidoarjo.go.id']);

        Livewire::test(Create::class)
            ->set('nama', 'Duplikat')
            ->set('email', 'dup@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('simpan')
            ->assertHasErrors(['email']);
    }

    public function test_pengguna_edit_screen_loads_existing_values(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $pengguna = Pengguna::factory()->create(['nama' => 'Nama Lama']);

        Livewire::test(Edit::class, ['pengguna' => $pengguna])
            ->assertSet('nama', 'Nama Lama');
    }

    public function test_pengguna_edit_updates_without_changing_password_when_blank(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $pengguna = Pengguna::factory()->create(['nama' => 'Nama Lama']);
        $originalPassword = $pengguna->password;

        Livewire::test(Edit::class, ['pengguna' => $pengguna])
            ->set('nama', 'Nama Baru')
            ->call('simpan')
            ->assertRedirect(route('app.pengguna.index'));

        $this->assertSame($originalPassword, $pengguna->fresh()->password);
        $this->assertSame('Nama Baru', $pengguna->fresh()->nama);
    }

    public function test_pengguna_edit_can_change_password(): void
    {
        $this->actingAs(Pengguna::factory()->create(), 'web');

        $pengguna = Pengguna::factory()->create();

        Livewire::test(Edit::class, ['pengguna' => $pengguna])
            ->set('password', 'sandibaru123')
            ->call('simpan')
            ->assertRedirect(route('app.pengguna.index'));

        $this->assertTrue(Hash::check('sandibaru123', $pengguna->fresh()->password));
    }
}

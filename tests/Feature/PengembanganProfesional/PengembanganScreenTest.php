<?php

namespace Tests\Feature\PengembanganProfesional;

use App\Livewire\PengembanganProfesional\FormRekomendasiManual;
use App\Livewire\PengembanganProfesional\KatalogMateri;
use App\Livewire\PengembanganProfesional\WidgetRekomendasiGuru;
use App\Models\MateriPengembangan;
use App\Models\RekomendasiPengembangan;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class PengembanganScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_katalog_materi_requires_authentication(): void
    {
        $this->get('/materi-pengembangan')->assertRedirect(route('login'));
    }

    public function test_guru_can_view_katalog_but_not_see_management_actions(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');
        MateriPengembangan::factory()->create(['judul' => 'Materi Terlihat Guru']);

        $this->get('/materi-pengembangan')
            ->assertOk()
            ->assertSee('Materi Terlihat Guru')
            ->assertDontSee('Tambah Materi');
    }

    public function test_guru_cannot_create_materi_via_katalog(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        Livewire::test(KatalogMateri::class)->call('bukaForm')->assertForbidden();
    }

    public function test_admin_dinas_can_create_materi_via_katalog(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');

        Livewire::test(KatalogMateri::class)
            ->call('bukaForm')
            ->set('judul', 'Pelatihan Asesmen Formatif')
            ->set('kategori', 'profesional')
            ->call('simpan');

        $this->assertDatabaseHas('materi_pengembangan', ['judul' => 'Pelatihan Asesmen Formatif']);
    }

    public function test_admin_dinas_can_delete_materi_via_katalog(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');
        $materi = MateriPengembangan::factory()->create();

        Livewire::test(KatalogMateri::class)->call('hapus', $materi->id);

        $this->assertSoftDeleted('materi_pengembangan', ['id' => $materi->id]);
    }

    public function test_widget_rekomendasi_guru_shows_own_rekomendasi(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $this->actingAs($guru, 'web');
        $materi = MateriPengembangan::factory()->create(['judul' => 'Materi Unik Untuk Diuji']);
        RekomendasiPengembangan::factory()->create(['pengguna_id' => $guru->id, 'materi_id' => $materi->id]);

        Livewire::test(WidgetRekomendasiGuru::class)->assertSee('Materi Unik Untuk Diuji');
    }

    public function test_widget_rekomendasi_guru_bisa_ubah_status(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $this->actingAs($guru, 'web');
        $rekomendasi = RekomendasiPengembangan::factory()->create(['pengguna_id' => $guru->id]);

        Livewire::test(WidgetRekomendasiGuru::class)
            ->call('ubahStatus', $rekomendasi->id, 'selesai');

        $this->assertDatabaseHas('rekomendasi_pengembangan', ['id' => $rekomendasi->id, 'status' => 'selesai']);
    }

    public function test_form_rekomendasi_manual_requires_authentication(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->get("/sesi-supervisi/{$sesi->id}/rekomendasi")->assertRedirect(route('login'));
    }

    public function test_supervisor_bisa_rekomendasikan_materi_via_livewire(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        $materi = MateriPengembangan::factory()->create();
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormRekomendasiManual::class, ['sesiSupervisi' => $sesi])
            ->set('materi_id', $materi->id)
            ->call('simpan')
            ->assertRedirect(route('app.sesi-supervisi.index'));

        $this->assertDatabaseHas('rekomendasi_pengembangan', [
            'sesi_id' => $sesi->id,
            'materi_id' => $materi->id,
            'sumber' => 'manual',
        ]);
    }

    public function test_guru_cannot_open_form_rekomendasi_manual(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        $this->actingAs($guru, 'web');

        Livewire::test(FormRekomendasiManual::class, ['sesiSupervisi' => $sesi])->assertForbidden();
    }
}

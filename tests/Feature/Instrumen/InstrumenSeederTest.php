<?php

namespace Tests\Feature\Instrumen;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use Database\Seeders\InstrumenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstrumenSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_one_instrumen_with_20_butir(): void
    {
        $this->seed(InstrumenSeeder::class);

        $this->assertSame(1, InstrumenObservasi::count());
        $this->assertSame(20, ButirObservasi::count());
    }

    public function test_seeded_instrumen_is_not_terkunci(): void
    {
        $this->seed(InstrumenSeeder::class);

        $instrumen = InstrumenObservasi::first();

        $this->assertFalse($instrumen->terkunci);
    }

    public function test_seeded_butir_cover_10_distinct_dimensions(): void
    {
        $this->seed(InstrumenSeeder::class);

        $this->assertSame(10, ButirObservasi::distinct()->count('dimensi'));
    }

    public function test_seeded_butir_are_marked_as_placeholder(): void
    {
        $this->seed(InstrumenSeeder::class);

        $butir = ButirObservasi::first();

        $this->assertStringContainsString('PLACEHOLDER', $butir->teks);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(InstrumenSeeder::class);
        $this->seed(InstrumenSeeder::class);

        $this->assertSame(1, InstrumenObservasi::count());
        $this->assertSame(20, ButirObservasi::count());
    }
}

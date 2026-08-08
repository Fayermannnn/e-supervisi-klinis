<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    /**
     * RefreshDatabase membungkus tiap test dalam transaction yang di-rollback;
     * cache permission spatie tidak tahu itu, jadi bisa merujuk role/permission
     * yang sudah "hilang" di test berikutnya bila tidak dibersihkan tiap test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

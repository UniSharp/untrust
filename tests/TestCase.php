<?php

namespace Tests;

use Closure;
use Mockery as m;
use CreateUsersTable;
use Tests\Models\Role;
use Tests\Models\User;
use CreateUntrustTables;
use Tests\Models\Permission;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Config\Repository as ConfigRepository;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $migrations = [
        CreateUsersTable::class,
        CreateUntrustTables::class,
    ];

    public function setUp()
    {
        $this->setMocks();

        $this->migrate();
    }

    public function tearDown()
    {
        $this->migrateRollback();

        m::close();
    }

    protected function setMocks()
    {
        $app = m::mock(Container::class);
        $app->shouldReceive('instance');
        $app->shouldReceive('offsetGet')->with('db')->andReturn(
            m::mock('db')->shouldReceive('connection')->andReturn(
                m::mock('connection')->shouldReceive('getSchemaBuilder')->andReturn('schema')->getMock()
            )->getMock()
        );
        $app->shouldReceive('offsetGet');

        Schema::setFacadeApplication($app);
        Schema::swap(Manager::Schema());

        Config::shouldReceive('get')->andReturnUsing(function ($key, $default = null) {
            $configs = [
                'untrust.user' => User::class,
                'untrust.role' => Role::class,
                'untrust.permission' => Permission::class,
            ];

            if (array_key_exists($key, $configs)) {
                return $configs[$key];
            }

            return $default;
        });

        Cache::shouldReceive('store')->andReturnSelf();
        Cache::shouldReceive('rememberForever')->andReturnUsing(function ($key, Closure $callback) {
            return $callback();

            static $caches = [];

            if (!array_key_exists($key, $caches)) {
                $caches[$key] = $callback();
            }

            return $caches[$key];
        });
    }

    protected function migrate()
    {
        foreach ($this->migrations as $migration) {
            (new $migration)->up();
        }
    }

    protected function migrateRollback()
    {
        foreach (array_reverse($this->migrations) as $migration) {
            (new $migration)->down();
        }
    }
}

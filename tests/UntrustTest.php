<?php

namespace Tests;

use Tests\Models\User;

class UntrustTest extends TestCase
{
    public function testRelations()
    {
        $user = User::create([
            'name' => 'foo',
            'email' => 'foo@example.com',
            'password' => 'password',
        ]);

        $roleA = $user->roles()->create(['name' => 'role-a']);
        $roleB = $user->roles()->create(['name' => 'role-b']);

        $roleA->permissions()->create(['name' => 'permission-a']);
        $roleA->permissions()->create(['name' => 'permission-b']);
        $roleB->permissions()->create(['name' => 'permission-c']);
        $user->permissions()->create(['name' => 'permission-d']);

        $this->assertEquals(
            ['role-a', 'role-b'],
            $user->roles->pluck('name')->toArray()
        );
        $this->assertEquals(
            ['permission-a', 'permission-b'],
            $roleA->permissions->pluck('name')->toArray()
        );
        $this->assertEquals(
            ['permission-c'],
            $roleB->permissions->pluck('name')->toArray()
        );
        $this->assertEquals(
            ['permission-a', 'permission-b', 'permission-c', 'permission-d'],
            $user->permissions->pluck('name')->toArray()
        );
    }

    public function testHasRole()
    {
        $user = User::create([
            'name' => 'bar',
            'email' => 'bar@example.com',
            'password' => 'password',
        ]);

        $role = $user->roles()->create(['name' => 'bar']);

        $this->assertTrue($user->hasRole($role));
        $this->assertTrue($user->hasRole('bar'));

        $this->assertFalse($user->hasRole('foo'));
    }

    public function testHasPermission()
    {
        $user = User::create([
            'name' => 'biz',
            'email' => 'biz@example.com',
            'password' => 'password',
        ]);

        $role = $user->roles()->create(['name' => 'biz']);
        $permission = $user->permissions()->create(['name' => 'biz']);

        $this->assertTrue($user->hasPermission($permission));
        $this->assertTrue($user->hasPermission('biz'));

        $permission = $role->permissions()->create(['name' => 'baz']);

        $this->assertTrue($user->hasPermission($permission));
        $this->assertTrue($user->hasPermission('baz'));

        $this->assertFalse($user->hasPermission('foo'));
    }

    public function testIsRoot()
    {
        $user = User::create([
            'name' => 'root',
            'email' => 'root@example.com',
            'password' => 'password',
        ]);

        $user->roles()->create(['name' => 'root']);

        $this->assertTrue($user->isRoot());
    }

    public function testIsNotRoot()
    {
        $user = User::create([
            'name' => 'notRoot',
            'email' => 'notRoot@example.com',
            'password' => 'password',
        ]);

        $this->assertFalse($user->isRoot());
    }
}
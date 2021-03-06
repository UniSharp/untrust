<?php

namespace Tests;

use Illuminate\Support\Facades\Config;
use Tests\Models\Permission;
use Tests\Models\Role;
use Tests\Models\User;

class UntrustTest extends TestCase
{
    public function testRelations()
    {
        $userA = User::create([
            'name' => 'user-a',
            'email' => 'user-a@example.com',
            'password' => 'password',
        ]);
        $userB = User::create([
            'name' => 'user-b',
            'email' => 'user-b@example.com',
            'password' => 'password',
        ]);

        $roleA = $userA->roles()->create(['name' => 'role-a']);
        $roleB = $userA->roles()->create(['name' => 'role-b']);
        $userB->roles()->attach($roleA);

        $roleA->permissions()->create(['name' => 'permission-a']);
        $roleA->permissions()->create(['name' => 'permission-b']);
        $roleB->permissions()->create(['name' => 'permission-c']);
        $userA->permissions()->create(['name' => 'permission-d']);

        $this->assertEquals(
            ['role-a', 'role-b'],
            $userA->roles->pluck('name')->toArray()
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
            $userA->permissions->pluck('name')->toArray()
        );
        $this->assertEquals(
            ['user-a', 'user-b'],
            $roleA->users->pluck('name')->toArray()
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

    public function testMultiRole()
    {
        $user = User::create([
            'name' => 'bar',
            'email' => 'bar@example.com',
            'password' => 'password',
        ]);

        $roleFoo = $user->roles()->create(['name' => 'foo']);
        $roleBar = $user->roles()->create(['name' => 'bar']);

        $this->assertTrue($user->hasRole($roleFoo, $roleBar));
        $this->assertTrue($user->hasRole('foo', 'bar'));
        $this->assertTrue($user->hasRole(['foo', 'bar']));
        $this->assertTrue($user->hasRole('foo', 'a'));
        $this->assertFalse($user->hasRole('a', 'b'));
    }

    public function testMultiPermissions()
    {
        $user = User::create([
            'name' => 'biz',
            'email' => 'biz@example.com',
            'password' => 'password',
        ]);

        $role = $user->roles()->create(['name' => 'biz']);
        $permissionBiz = $user->permissions()->create(['name' => 'biz']);

        $permissionBaz = $role->permissions()->create(['name' => 'baz']);

        $this->assertTrue($user->hasPermission($permissionBiz, $permissionBaz));
        $this->assertTrue($user->hasPermission('biz', 'baz'));
        $this->assertTrue($user->hasPermission('biz', 'a'));

        $this->assertFalse($user->hasPermission('foo', 'bar'));
    }

    public function testRolesOfPermissions()
    {
        $role = Role::create(['name' => 'foo']);
        $permission = $role->permissions()->create(['name' => 'biz']);
        $this->assertEquals(1, $permission->roles->where('name', 'foo')->count());
    }

    public function testUsersOfPermissions()
    {
        $userBiz = User::create([
            'name' => 'biz',
            'email' => 'biz@example.com',
            'password' => 'password',
        ]);
        $userBaz = User::create([
            'name' => 'baz',
            'email' => 'baz@example.com',
            'password' => 'password',
        ]);

        $role = Role::create(['name' => 'foo']);
        $permission = Permission::create(['name' => 'bar']);

        $userBiz->permissions()->attach($permission);
        $role->permissions()->attach($permission);
        $userBaz->roles()->attach($role);

        $this->assertEquals(2, $permission->users->count());
        $this->assertEquals(1, $permission->users->where('name', 'biz')->count());
        $this->assertEquals(1, $permission->users->where('name', 'baz')->count());
    }
}

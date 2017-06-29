<?php

namespace UniSharp\Untrust\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

trait UserTrait
{
    public function roles()
    {
        return $this->belongsToMany(
            Config::get('untrust.role', \App\Role::class),
            Config::get('untrust.user_roles_table', 'user_roles')
        )->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Config::get('untrust.permission', \App\Permission::class),
            Config::get('untrust.user_permissions_table', 'user_permissions')
        )->withTimestamps();
    }

    public function getRolesAttribute()
    {
        return Cache::store('array')->rememberForever("user.{$this->id}.roles", function () {
            return $this->roles()->get();
        });
    }

    public function getPermissionsAttribute()
    {
        return Cache::store('array')->rememberForever("user.{$this->id}.permissions", function () {
            return new Collection($this->roles()->with('permissions')->get()->pluck('permissions')->collapse()->merge(
                $this->permissions()->get()
            ));
        });
    }

    public function hasRole(...$roles)
    {
        $roles = count($roles) == 1 && is_array($roles[0]) ? $roles[0] : $roles;

        foreach ($roles as $role) {
            if (!($role instanceof Model)) {
                $role = $this->roles->where('name', $role)->first();
            }

            if ($this->roles->contains($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(...$permissions)
    {
        $permissions = count($permissions) == 1 && is_array($permissions[0]) ? $permissions[0] : $permissions;

        foreach ($permissions as $permission) {
            if (!($permission instanceof Model)) {
                $permission = $this->permissions->where('name', $permission)->first();
            }

            if ($this->permissions->contains($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isRoot()
    {
        return $this->hasRole('root');
    }
}

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

    public function hasRole($role)
    {
        if (!($role instanceof Model)) {
            $role = $this->roles->where('name', $role)->first();
        }

        return $this->roles->contains($role);
    }

    public function hasPermission($permission)
    {
        if (!($permission instanceof Model)) {
            $permission = $this->permissions->where('name', $permission)->first();
        }

        return $this->permissions->contains($permission);
    }

    public function isRoot()
    {
        return $this->hasRole('root');
    }
}

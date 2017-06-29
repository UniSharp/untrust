<?php

namespace UniSharp\Untrust\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

trait PermissionTrait
{
    public function roles()
    {
        return $this->belongsToMany(
            Config::get('untrust.role', \App\Role::class),
            Config::get('untrust.role_permissions_table', 'role_permissions')
        )->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(
            Config::get('untrust.user', \App\User::class),
            Config::get('untrust.user_permissions_table', 'user_permissions')
        )->withTimestamps();
    }

    public function getUsersAttribute()
    {
        return Cache::store('array')->rememberForever("permissions.{$this->id}.users", function () {
            return $this->users()->get()->merge(
                $this->roles->map(function ($role) {
                    return $role->users;
                })->collapse()
            );
        });
    }
}

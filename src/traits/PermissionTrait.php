<?php

namespace UniSharp\Untrust\Traits;

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
}

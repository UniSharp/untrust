<?php

namespace UniSharp\Untrust\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

trait RoleTrait
{
    public function permissions()
    {
        return $this->belongsToMany(
            Config::get('untrust.permission', \App\Permission::class),
            Config::get('untrust.role_permissions_table', 'role_permissions')
        )->withTimestamps();
    }
}

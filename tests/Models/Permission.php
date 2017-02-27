<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use UniSharp\Untrust\Traits\PermissionTrait;

class Permission extends Model
{
    use PermissionTrait;

    protected $fillable = ['name'];
}

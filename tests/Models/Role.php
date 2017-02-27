<?php

namespace Tests\Models;

use UniSharp\Untrust\Traits\RoleTrait;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use RoleTrait;

    protected $fillable = ['name'];
}

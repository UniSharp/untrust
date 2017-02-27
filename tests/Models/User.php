<?php

namespace Tests\Models;

use UniSharp\Untrust\Traits\UserTrait;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use UserTrait;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];
}

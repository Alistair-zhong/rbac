<?php

namespace Rbac\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Authenticator extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;

    // laravel-mongodb 5 的缺省主键是 id，显式设成 _id，与 Rbac\Models\Model 一致
    protected $primaryKey = '_id';
}

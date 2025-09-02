<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class RoleGroup extends Model
{
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function users()
    {
        return $this->hasMany(User::class,'role_group_id','id');
    }
}

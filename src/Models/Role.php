<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function crud()
    {
        return $this->belongsTo(Crud::class, 'crud_id');
    }
}

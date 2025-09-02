<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class CrudItem extends Model
{
    public function type()
    {
        return $this->belongsTo(FormType::class,'form_type_id','id');
    }
}

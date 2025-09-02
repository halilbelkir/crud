<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    public function crud($route)
    {
        return Crud::whereLike('slug', '%'.$route.'%')->first();
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }
}

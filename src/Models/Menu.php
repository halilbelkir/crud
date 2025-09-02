<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('order','asc');
    }

    public function parentItems()
    {
        return $this->hasMany(MenuItem::class)->where('parent_id', 0)->orderBy('order');
    }

}

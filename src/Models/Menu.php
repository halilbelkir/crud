<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('order','asc');
    }

    public function noMainItems()
    {
        return $this->hasMany(MenuItem::class)->orderBy('order','asc')->where('main',0);
    }

    public function parentItems()
    {
        return $this->hasMany(MenuItem::class)->where('parent_id', 0)->orderBy('order');
    }

    public function noMainParentItems()
    {
        return $this->hasMany(MenuItem::class)->where('parent_id', 0)->orderBy('order')->where('main',0);
    }

}

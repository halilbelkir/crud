<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class Crud extends Model
{
    public function roles($roleGroupId)
    {
        return $this->hasMany(Role::class)->where('role_group_id',$roleGroupId)->first();
    }

    public function items()
    {
        return $this->hasMany(CrudItem::class)->orderBy('order');
    }

    public function menuItems()
    {
        $slug = $this->slug;

        return MenuItem::whereLike('route','%'.$slug.'%')->first();
    }

    public function getRoles()
    {
        return $this->hasMany(Role::class);
    }

    public function browseColumns()
    {
        return $this->hasMany(CrudItem::class)->orderBy('order')->where('browse',1);
    }

    public function getRelationships()
    {
        return $this->hasMany(CrudItem::class)->orderBy('order')->where('relationship',1);
    }

    public function addColumns()
    {
        return $this->hasMany(CrudItem::class)->orderBy('order')->where('add',1);
    }

    public function editColumns()
    {
        return $this->hasMany(CrudItem::class)->orderBy('order')->where('edit',1);
    }

    public function readColumns()
    {
        return $this->hasMany(CrudItem::class)->orderBy('order')->where('read',1);
    }

    public function getColumn($column)
    {
        return $this->hasMany(CrudItem::class)->where('crud_items.column_name',$column)->first();
    }
}

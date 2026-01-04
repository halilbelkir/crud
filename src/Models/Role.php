<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
{
    public function crud()
    {
        return $this->belongsTo(Crud::class, 'crud_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function scopeForRoute(Builder $query, string $route): Builder
    {
        return $query->where(function ($q) use ($route) {
            $q->whereHas('crud', function ($q) use ($route) {
                $q->where('slug', 'like', "%{$route}%");
            })
                ->orWhereHas('menuItem', function ($q) use ($route) {
                    $q->where('route' , "{$route}");
                });
        });
    }
}

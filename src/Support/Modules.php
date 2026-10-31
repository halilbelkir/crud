<?php

namespace crudPackage\Support;

use crudPackage\Models\Crud;
use Illuminate\Support\Facades\Schema;

class Modules
{
    public static function ordering(): array
    {
        return once(function ()
        {
            if (! Schema::hasTable('cruds'))
            {
                return [];
            }

            return Crud::where('main', 0)
                ->get(['slug', 'area_1'])
                ->mapWithKeys(function (Crud $crud)
                {
                    $area = json_decode((string) $crud->area_1, true) ?? [];

                    return [$crud->slug =>
                    [
                        'column'    => filled($area['order_column_name'] ?? null) ? $area['order_column_name'] : null,
                        'direction' => ($area['order_direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
                    ]];
                })
                ->all();
        });
    }

    public static function models(): array
    {
        return once(function ()
        {
            if (! Schema::hasTable('cruds'))
            {
                return [];
            }

            return Crud::where('main', 0)
                ->get(['slug', 'model'])
                ->filter(fn (Crud $crud) => filled($crud->model) && class_exists($crud->model))
                ->mapWithKeys(fn (Crud $crud) => [$crud->slug => $crud->model])
                ->all();
        });
    }
}

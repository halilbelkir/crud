<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\DataTranslate;
use crudPackage\Support\Permissions;
use crudPackage\Support\Sortable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReorderController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(
        [
            'model'   => ['required', 'string', Rule::in(array_keys(Sortable::models()))],
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        Permissions::authorize(Sortable::permission($data['model']));

        $model     = Sortable::modelFor($data['model']);
        $column    = Sortable::column($data['model']);
        $direction = Sortable::direction($data['model']);
        $locale    = $request->query('locale');
        $count     = count($data['order']);

        abort_if($column === null, 422);

        foreach ($data['order'] as $index => $id)
        {
            $position = $direction === 'desc' ? $count - $index : $index + 1;

            if ($locale && is_array(multipleLanguages()) && multipleLanguages(2, $locale))
            {
                $translate = DataTranslate::where('model', $model)
                    ->where('locale', $locale)
                    ->where('foreign_key', $id)
                    ->where('column_name', $column)
                    ->first() ?? new DataTranslate();

                $translate->model       = $model;
                $translate->locale      = $locale;
                $translate->foreign_key = $id;
                $translate->column_name = $column;
                $translate->value       = $position;
                $translate->save();

                continue;
            }

            $model::where((new $model)->getKeyName(), $id)->update([$column => $position]);
        }

        return response()->json(
        [
            'status' => 'Sıralama güncellendi.',
        ]);
    }
}

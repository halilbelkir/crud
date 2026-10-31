<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use crudPackage\Models\CrudItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class MainController extends Controller
{
    public function crud(Request $request,Crud $crud)
    {
        try
        {
            return response()->json(
                [
                    'result'   => 1,
                    'response' => $crud,
                ]
            );

        }
        catch (\Exception $e)
        {
            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }

    public function relationOptions(Crud $crud, string $column, Request $request)
    {
        try
        {
            $item = CrudItem::where('crud_id', $crud->id)->where('column_name', $column)->firstOrFail();

            $details = json_decode($item->detail, true);

            abort_unless(!empty($details['depends_on']) && !empty($details['model']), 404);

            $model       = $details['model'];
            $parentValue = $request->query('parent');

            $options = $parentValue !== null && $parentValue !== ''
                ? $model::where($details['depends_on']['column'], $parentValue)
                    ->orderBy($details['show_column'])
                    ->get()
                    ->map(function ($option) use ($details)
                    {
                        return [
                            'value' => $option->{$details['match_column']},
                            'text'  => $option->{$details['show_column']},
                        ];
                    })
                    ->values()
                : collect();

            return response()->json(
                [
                    'result'  => 1,
                    'options' => $options,
                ]
            );
        }
        catch (\Exception $e)
        {
            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }
}

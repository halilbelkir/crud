<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use crudPackage\Models\CrudItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class MainController extends Controller
{
    public function statusUpdate(Request $request)
    {
        try
        {
            $attribute =
                [
                    'status' => 'Durum',
                    'id'     => 'ID',
                ];

            $rules =
                [
                    'status' => 'required',
                    'id'     => 'required',
                ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attribute);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result' => 2,
                        'message' => $validator->errors()
                    ],403
                );
            }

            $status        = $request->get('status');
            $id            = $request->get('id');
            $modelName     = 'App\Models\\'.$request->get('modelName');
            $value         = $modelName::find($id);
            $value->status = $status == 0 ? 1 : 0;
            $value->save();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.'
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

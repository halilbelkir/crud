<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use Illuminate\Http\Request;
use Mockery\Exception;
use Session;
use Validator;


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
        catch (Exception $e)
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
        catch (Exception $e)
        {
            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }
}

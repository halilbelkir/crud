<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use crudPackage\Models\Language;
use Validator;
use crudPackage\Library\ImageUpload\ImageUpload;
class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $value     = Setting::first();
        $languages = Language::orderBy('order','asc')->get();

        return view('crudPackage::settings.index',compact('value','languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try
        {
            $data      = Setting::find($id);
            $attribute =
                [
                    'title'     => 'Başlık',
                    'subtitle'  => 'Karşılama Mesajı',
                ];

            $rules =
                [
                    'title'     => 'required',
                    'subtitle'  => 'required',
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

            $imageUpload = new ImageUpload();

            if ($request->has('logo'))
            {
                $imageUpload->delete($data->logo);

                $name       = 'logo-'.random_int(100000000, 999999999999).time();
                $fileName    = $imageUpload->getName($request->file('logo'),$name,'settings');
                $data->logo = $fileName;
            }

            if ($request->has('icon'))
            {
                $imageUpload->delete($data->icon);

                $name       = 'icon-'.random_int(100000000, 999999999999).time();
                $fileName    = $imageUpload->getName($request->file('icon'),$name,'settings');
                $data->icon = $fileName;
            }

            if ($request->has('loader'))
            {
                $imageUpload->delete($data->loader);

                $name         = 'loader-'.random_int(100000000, 999999999999).time();
                $fileName      = $imageUpload->getName($request->file('loader'),$name,'settings');
                $data->loader = $fileName;
            }

            if ($request->has('bg_image'))
            {
                $imageUpload->delete($data->bg_image);

                $name           = 'bg_image-'.random_int(100000000, 999999999999).time();
                $fileName        = $imageUpload->getName($request->file('bg_image'),$name,'settings');
                $data->bg_image = $fileName;
            }

            $data->title     = $request->get('title');
            $data->subtitle  = $request->get('subtitle');
            $data->color_1   = $request->get('color_1');
            $data->color_2   = $request->get('color_2');
            $data->languages = $request->get('languages');
            $data->save();

            Cache::forget('settings');

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route('settings.index')
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

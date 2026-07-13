<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Http\Controllers\Controller;
use crudPackage\Library\ImageUpload\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
class MediaController extends Controller
{
    protected string $disk = 'upload';

    protected function diskConfig(string $key = null)
    {
        $disk = $this->disk;

        return $key
            ? config("filesystems.disks.$disk.$key")
            : config("filesystems.disks.$disk");
    }


    public function index($path = null)
    {
        abort_if(!preg_match('#^[a-zA-Z0-9/_-]*$#', $path), 403);

        $segments = $path ? explode('/', $path) : [];

        $folders = collect(Storage::disk($this->disk)->directories($path))
            ->sortBy(fn ($folder) => mb_strtolower(basename($folder)), SORT_NATURAL)
            ->values()
            ->all();

        $files = collect(Storage::disk($this->disk)->files($path))
            ->sortBy(fn ($file) => mb_strtolower(basename($file)), SORT_NATURAL)
            ->values()
            ->all();

        $disk = $this->disk;

        return view('crudPackage::media.index', compact('folders', 'files', 'segments','path', 'disk'));
    }

    public function download($path = null)
    {
        abort_if(!$path || str_contains($path, '..'), 403);
        abort_if(!Storage::disk($this->disk)->fileExists($path), 404);

        return Storage::disk($this->disk)->download($path);
    }

    public function upload(Request $request)
    {
        try
        {
            $attribute =
                [
                    'files' => 'Dosya',
                ];

            $rules =
                [
                    'files' => 'required',
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

            $path = $request->path ?? '';

            foreach ($request->file('files') as $order => $mFile)
            {
                $originalName = pathinfo($mFile->getClientOriginalName(), PATHINFO_FILENAME);
                $extension    = $mFile->getClientOriginalExtension();
                $extension    = Str::slug($extension);
                $slugName     = Str::slug($originalName);
                $fileName      = $slugName . '.' . $extension;

                $i = 1;

                while (Storage::disk($this->disk)->exists($path.'/'.$fileName))
                {
                    $fileName = $slugName . '-' . $i++ . '.' . $extension;
                }

                $mFile->storeAs($path, $fileName, $this->disk);
            }

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
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

    public function createFolder(Request $request)
    {
        try
        {
            $attribute =
                [
                    'name' => 'Klasör Adı',
                ];

            $rules =
                [
                    'name' => 'required',
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

            Storage::disk($this->disk)->makeDirectory($request->path . '/' . $request->name);

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
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

    public function delete(Request $request,$path = null)
    {
        try
        {
            $disk = Storage::disk($this->disk);

            if ($disk->directoryExists($path))
            {
                $disk->deleteDirectory($path);
            }
            elseif ($disk->fileExists($path))
            {
                $disk->delete($path);
            }

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
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

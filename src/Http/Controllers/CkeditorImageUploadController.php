<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Library\ImageUpload\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class CkeditorImageUploadController extends Controller
{
    public function storeImage(Request $request)
    {
        $fileSelector = $request->hasFile('upload') ? 'upload' : 'file';

        $validator = validator($request->all(),
        [
            $fileSelector => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:4096'],
        ],
        [
            $fileSelector . '.required' => 'Görsel seçilmedi.',
            $fileSelector . '.mimes'    => 'Yalnızca jpg, png, webp, gif ve svg yüklenebilir.',
            $fileSelector . '.max'      => 'Görsel en fazla 4 MB olabilir.',
        ]);

        if ($validator->fails())
        {
            return response()->json(
            [
                'error' => ['message' => $validator->errors()->first($fileSelector)],
            ], 422);
        }

        if ($request->hasFile($fileSelector))
        {
            $file      = $request->file($fileSelector);
            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['jpg', 'jpeg', 'png']))
            {
                $manager   = new ImageManager(new Driver());
                $imageName = 'editor/' . $this->randomNameGenerator() . '.webp';

                Storage::disk('upload')->put($imageName, (string) $manager->read($file)->toWebp(80));
            }
            else
            {
                $imageUpload = new ImageUpload();
                $imageName   = $imageUpload->getName($file, $this->randomNameGenerator(), 'editor');
            }

            $url = Storage::disk('upload')->url($imageName);

            return response()->json(
                [
                    'url'      => $url,
                    'location' => $url,
                ]
            );

        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function randomNameGenerator()
    {
        return 'editor-'.random_int(100000000, 999999999999);
    }
}

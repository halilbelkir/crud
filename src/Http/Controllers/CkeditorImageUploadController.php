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
        $fileSelector = 'file';

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

            return response()->json(
                [
                    'location' => Storage::disk('upload')->url($imageName)
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

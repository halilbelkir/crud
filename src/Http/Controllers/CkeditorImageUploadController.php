<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Library\ImageUpload\ImageUpload;
use Illuminate\Http\Request;


class CkeditorImageUploadController extends Controller
{
    public function storeImage(Request $request)
    {
        $fileSelector = 'file';

        if ($request->hasFile($fileSelector))
        {
            $extension   = $request->file($fileSelector)->getClientOriginalExtension();
            $fileName     = $this->randomNameGenerator().'.' . $extension;
            $imageUpload = new ImageUpload();
            $imageName   = $imageUpload->getName($request->file($fileSelector),$this->randomNameGenerator(),'editor');

            return response()->json(['location' => $imageName]);

        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function randomNameGenerator()
    {
        return 'editor-'.random_int(100000000, 999999999999);
    }
}

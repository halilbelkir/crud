<?php

namespace crudPackage\Library\ImageUpload;

use crudPackage\Library\Cdn\Cdn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;


class ImageUpload
{
    private $storage;

    public function __construct()
    {
        $this->storage =  Storage::disk('upload');
    }

    public function setImageName($image,$title,$table)
    {
        $imageName    = Str::slug($title, '-').'-'.time().'.'.$image->extension();
        $target_file   = $table;
        $imageName    = $target_file.'/'.$imageName;
        $imageName    = [$imageName,$target_file];

        return $imageName;
    }

    public function getName($image,$title,$table)
    {
        $imageName = $this->setImageName($image,$title,$table);
        $getImage  = $this->set($image,$imageName);

        return $getImage;
    }
    public function set($image,$imageName)
    {
        $manager = new ImageManager(new Driver());
        $img     = $manager->read(file_get_contents($image));
        $encode  = $img->encodeByExtension($image->extension());
        $fileName = $imageName[0];

        $this->getStorage()->put($fileName, $encode->toString());

        return $this->getStorage()->url($fileName);
    }

    public function delete($image)
    {
        $image = str_replace('/upload/', '', $image);

        $this->storage->delete($image);
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getStorage(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return $this->storage;
    }

    public function resize($image, $width, $height, $title,$table)
    {
        $manager   = new ImageManager(new Driver());
        $img       = $manager->read(file_get_contents($image));
        $resize    = $img->cover($width, $height);
        $encode    = $img->encodeByExtension($image->extension());
        $imageName = $this->setImageName($image,$title,$table);

        return $this->set($image,$imageName);
    }

    public function copy($oldFile,$newFile)
    {
        $oldFile = str_replace('/upload/', '', $oldFile);

        $this->getStorage()->copy($oldFile, $newFile);

        return $this->getStorage()->url($newFile);
    }
}
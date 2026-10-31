<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Http\Controllers\Controller;
use crudPackage\Library\ImageUpload\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class MediaController extends Controller
{
    protected string $disk = 'upload';

    protected function diskConfig(?string $key = null)
    {
        $disk = $this->disk;

        return $key
            ? config("filesystems.disks.$disk.$key")
            : config("filesystems.disks.$disk");
    }


    public function index($path = null)
    {
        abort_if(!preg_match('#^[a-zA-Z0-9/_-]*$#', (string) $path), 403);

        $segments = $path ? explode('/', $path) : [];
        $hidden   = config('crudPanel.media.hidden', []);

        abort_if(collect($hidden)->contains(fn ($folder) => $path === $folder || str_starts_with((string) $path, $folder . '/')), 404);

        $folders = collect(Storage::disk($this->disk)->directories($path))
            ->reject(fn ($folder) => in_array($folder, $hidden, true))
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

    public function upload(Request $request): RedirectResponse
    {
        $request->validate(
        [
            'path'    => ['nullable', 'string', 'regex:#^[a-zA-Z0-9/_-]*$#'],
            'files'   => ['required', 'array'],
            'files.*' => ['file', 'max:20480'],
        ],
        [],
        [
            'files'   => 'Dosya',
            'files.*' => 'Dosya',
        ]);

        $path = trim((string) $request->input('path'), '/');

        foreach ($request->file('files') as $mFile)
        {
            $originalName = pathinfo($mFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension    = Str::slug($mFile->getClientOriginalExtension());
            $slugName     = Str::slug($originalName);
            $fileName     = $slugName . '.' . $extension;

            $i = 1;

            while (Storage::disk($this->disk)->exists($path . '/' . $fileName))
            {
                $fileName = $slugName . '-' . $i++ . '.' . $extension;
            }

            $mFile->storeAs($path, $fileName, $this->disk);
        }

        return back()->with('status', count($request->file('files')) . ' dosya yüklendi.');
    }

    public function createFolder(Request $request): RedirectResponse
    {
        $data = $request->validate(
        [
            'path' => ['nullable', 'string', 'regex:#^[a-zA-Z0-9/_-]*$#'],
            'name' => ['required', 'string', 'max:100'],
        ],
        [],
        [
            'name' => 'Klasör Adı',
        ]);

        $name   = collect(explode('_', $data['name']))->map(fn ($part) => Str::slug($part))->filter()->implode('_');
        $folder = trim(trim((string) ($data['path'] ?? ''), '/') . '/' . $name, '/');

        if ($name === '' || Storage::disk($this->disk)->directoryExists($folder))
        {
            throw ValidationException::withMessages(
            [
                'name' => $name === '' ? 'Geçerli bir klasör adı girin.' : 'Bu adla bir klasör zaten var.',
            ]);
        }

        Storage::disk($this->disk)->makeDirectory($folder);
        chmod(Storage::disk($this->disk)->path($folder), 0775);

        return back()->with('status', $name . ' klasörü oluşturuldu.');
    }

    public function delete(Request $request, $path = null): RedirectResponse
    {
        abort_if(! $path || str_contains($path, '..'), 403);

        $disk = Storage::disk($this->disk);

        if ($disk->directoryExists($path))
        {
            $disk->deleteDirectory($path);
        }
        elseif ($disk->fileExists($path))
        {
            $disk->delete($path);
        }

        return back()->with('status', basename($path) . ' silindi.');
    }
}

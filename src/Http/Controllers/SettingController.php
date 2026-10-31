<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use crudPackage\Models\Language;
use Illuminate\Support\Facades\Validator;
use crudPackage\Library\ImageUpload\ImageUpload;
use crudPackage\Library\Theme\ThemeManager;
class SettingController extends Controller
{
    public function index()
    {
        $value       = Setting::first();
        $languages   = Language::orderBy('order','asc')->get();
        $fonts       = googleFonts();
        $themes      = ThemeManager::selectable();
        $activeTheme = ThemeManager::active();

        return view('crudPackage::settings.index',compact('value','languages','fonts','themes','activeTheme'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $data  = Setting::findOrFail($id);
        $image = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:4096'];

        $validated = $request->validate(
        [
            'title'       => ['required', 'string', 'max:255'],
            'subtitle'    => ['required', 'string', 'max:255'],
            'theme'       => ['required', 'in:' . implode(',', array_keys(ThemeManager::ready()))],
            'color_1'     => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_2'     => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'font'        => ['nullable', 'string', 'max:255'],
            'languages'   => ['nullable', 'array'],
            'languages.*' => ['integer'],
            'logo'        => $image,
            'icon'        => $image,
            'loader'      => $image,
            'bg_image'    => $image,
        ],
        [],
        [
            'title'    => 'Başlık',
            'subtitle' => 'Karşılama Mesajı',
            'theme'    => 'Tema',
            'color_1'  => 'Panel Renk 1',
            'color_2'  => 'Panel Renk 2',
            'logo'     => 'Logo',
            'icon'     => 'İkon',
            'loader'   => 'Yükleme Animasyonu',
            'bg_image' => 'Arka Plan Resmi',
        ]);

        $imageUpload = new ImageUpload();

        foreach (['logo', 'icon', 'loader', 'bg_image'] as $field)
        {
            if (! $request->hasFile($field) && $request->boolean($field . '_remove') && filled($data->{$field}))
            {
                $imageUpload->delete($data->{$field});

                $data->{$field} = null;

                continue;
            }

            if (! $request->hasFile($field))
            {
                continue;
            }

            $imageUpload->delete($data->{$field});

            $data->{$field} = $imageUpload->getName($request->file($field), $field . '-' . random_int(100000000, 999999999999) . time(), 'settings');
        }

        $data->title     = $validated['title'];
        $data->subtitle  = $validated['subtitle'];
        $data->color_1   = $validated['color_1'] ?? null;
        $data->color_2   = $validated['color_2'] ?? null;
        $data->font      = $validated['font'] ?? null;
        $data->theme     = $validated['theme'];
        $data->languages = array_map('strval', $validated['languages'] ?? []);
        $data->save();

        Cache::forget('settings');

        return redirect()->route('settings.index')->with('status', 'Ayarlar kaydedildi.');
    }
}

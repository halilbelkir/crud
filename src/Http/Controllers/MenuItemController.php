<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use crudPackage\Models\Menu;
use crudPackage\Models\MenuItem;
use crudPackage\Models\Role;
use crudPackage\Support\Menu as MenuSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function create(Request $request, Menu $menu): View
    {
        $item = (new MenuItem)->forceFill(['menu_id' => $menu->id, 'parent_id' => 0]);

        return $this->form($request, $menu, $item);
    }

    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $item          = new MenuItem;
        $item->menu_id = $menu->id;
        $item->order   = (int) $menu->items()->max('order') + 1;
        $item->main    = 0;

        $this->fill($request, $menu, $item);

        return redirect()->route('menus.show', $menu)->with('status', $item->title . ' eklendi.')->with('reload', true);
    }

    public function order(Request $request, Menu $menu): JsonResponse
    {
        $data = $request->validate(
        [
            'tree'              => ['required', 'array'],
            'tree.*.id'         => ['required', 'integer'],
            'tree.*.children'   => ['array'],
            'tree.*.children.*' => ['integer'],
        ]);

        $ids = collect($data['tree'])->flatMap(fn (array $node) => array_merge([$node['id']], $node['children'] ?? []));

        abort_if($ids->count() !== $ids->unique()->count(), 422);
        abort_if($menu->items()->where('main', 0)->whereIn('id', $ids)->count() !== $ids->count(), 422);

        DB::transaction(function () use ($data)
        {
            foreach ($data['tree'] as $order => $node)
            {
                MenuItem::whereKey($node['id'])->update(['order' => $order + 1, 'parent_id' => 0]);

                foreach ($node['children'] ?? [] as $childOrder => $child)
                {
                    MenuItem::whereKey($child)->update(['order' => $childOrder + 1, 'parent_id' => $node['id']]);
                }
            }
        });

        session()->flash('status', 'Sıralama güncellendi.');

        return response()->json(
        [
            'status' => 'Sıralama güncellendi.',
            'reload' => true,
        ]);
    }

    public function edit(Request $request, MenuItem $item): View
    {
        return $this->form($request, $item->menu, $item);
    }

    public function update(Request $request, MenuItem $item): RedirectResponse
    {
        abort_if($item->main == 1, 403);

        $this->fill($request, $item->menu, $item);

        return redirect()->route('menus.show', $item->menu_id)->with('status', 'Menü öğesi güncellendi.')->with('reload', true);
    }

    public function destroy(MenuItem $item): RedirectResponse
    {
        if ($item->main == 1)
        {
            return back()->with('error', 'Sistem öğeleri silinemez.');
        }

        MenuItem::where('parent_id', $item->id)->update(['parent_id' => 0]);
        Role::where('menu_item_id', $item->id)->delete();

        $item->delete();

        return redirect()->route('menus.show', $item->menu_id)->with('status', $item->title . ' silindi.')->with('reload', true);
    }

    protected function form(Request $request, Menu $menu, MenuItem $item): View
    {
        $cruds   = Crud::where('status', 1)->where('main', 0)->orderBy('title')->pluck('title', 'id')->all();
        $icons   = array_combine(MenuSupport::icons(), MenuSupport::icons());
        $parents = $menu->items()
            ->where('parent_id', 0)
            ->when($item->exists, fn ($query) => $query->whereKeyNot($item->id))
            ->pluck('title', 'id')
            ->all();

        $crudId = $item->exists && $item->dynamic_route == 1 ? Crud::where('slug', Str::before((string) $item->route, '.'))->value('id') : null;
        $crudId = isset($cruds[$crudId]) ? $crudId : null;

        return view($request->ajax() ? 'crudPackage::menus.items.form' : 'crudPackage::menus.items.edit', compact('menu', 'item', 'cruds', 'crudId', 'icons', 'parents'));
    }

    protected function fill(Request $request, Menu $menu, MenuItem $item): void
    {
        $data = $request->validate(
        [
            'crud_id'   => ['nullable', 'integer', Rule::exists('cruds', 'id')],
            'title'     => ['required_without:crud_id', 'nullable', 'string', 'max:255'],
            'route'     => ['required_without:crud_id', 'nullable', 'string', 'max:255'],
            'icon'      => ['nullable', Rule::in(MenuSupport::icons())],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $menu->id)->where('parent_id', 0)],
        ],
        [],
        [
            'crud_id'   => 'Ekran',
            'title'     => 'Başlık',
            'route'     => 'Url / Route',
            'icon'      => 'İkon',
            'parent_id' => 'Üst öğe',
        ]);

        $crud = isset($data['crud_id']) ? Crud::find($data['crud_id']) : null;

        if (! $crud && $request->boolean('dynamic_route') && ! Route::has($data['route']))
        {
            throw ValidationException::withMessages(
            [
                'route' => 'Bu adla bir route bulunamadı. Url giriyorsanız "Dinamik route" seçeneğini kapatın.',
            ]);
        }

        $item->title         = filled($data['title'] ?? null) ? $data['title'] : $crud?->title;
        $item->route         = $crud && Str::before((string) ($data['route'] ?? ''), '.') !== $crud->slug ? $crud->slug . '.index' : $data['route'];
        $item->icon          = $data['icon'] ?? null;
        $item->parent_id     = (int) ($data['parent_id'] ?? 0) === (int) $item->id ? 0 : (int) ($data['parent_id'] ?? 0);
        $item->dynamic_route = $crud ? 1 : ($request->boolean('dynamic_route') ? 1 : 0);
        $item->target        = $request->boolean('target') ? 1 : 0;
        $item->special       = $request->boolean('special') ? 1 : 0;
        $item->save();

        if ($item->special == 1 && ! Role::where('menu_item_id', $item->id)->where('role_group_id', 1)->exists())
        {
            $role                = new Role;
            $role->role_group_id = 1;
            $role->menu_item_id  = $item->id;
            $role->browse        = 1;
            $role->save();
        }
    }
}

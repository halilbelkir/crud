<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Menu;
use crudPackage\Models\MenuItem;
use crudPackage\Support\Actions;
use crudPackage\Support\Search;
use crudPackage\Support\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    public function index(Request $request): View|Response
    {
        $records = Menu::query()
            ->withCount('items')
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', Search::pattern((string) $request->string('q'))))
            ->orderBy('id');

        $columns = $this->columns();
        $records = Table::sort($records, $columns);

        if ($export = Table::export($records, $columns, 'Menüler'))
        {
            return $export;
        }

        $menus = $records->paginate(Table::perPage())->withQueryString();
        $total = Menu::count();

        return view('crudPackage::menus.index', compact('menus', 'columns', 'total'));
    }

    protected function columns(): array
    {
        return [
            [
                'type'     => 'lead',
                'label'    => 'Menü',
                'width'    => '1.6fr',
                'sortable' => 'title',
                'name'     => fn (Menu $menu) => $menu->title,
                'sub'      => fn (Menu $menu) => $this->locked($menu) ? 'Panel menüsü' : null,
            ],
            [
                'type'     => 'text',
                'label'    => 'Öğe',
                'width'    => '120px',
                'bold'     => true,
                'sortable' => 'items_count',
                'value'    => fn (Menu $menu) => $menu->items_count,
            ],
            [
                'type'     => 'status',
                'label'    => 'Durum',
                'width'    => '140px',
                'sortable' => 'status',
                'value'    => fn (Menu $menu) => $menu->status == 1 ? 'Aktif' : 'Pasif',
                'tone'     => fn (Menu $menu) => $menu->status == 1 ? 'success' : 'muted',
            ],
            [
                'type'   => 'actions',
                'width'  => '60px',
                'status' => fn (Menu $menu) => ! $this->locked($menu),
                'menu'   => fn (Menu $menu) => Actions::menu('menus', $menu,
                [
                    'editTitle' => 'Menüyü düzenle',
                    'except'    => $this->locked($menu) ? ['delete'] : [],
                ]),
            ],
        ];
    }

    protected function locked(Menu $menu): bool
    {
        return (int) $menu->getKey() === 1;
    }

    public function create(Request $request): View
    {
        $menu = new Menu;

        return view($request->ajax() ? 'crudPackage::menus.form' : 'crudPackage::menus.edit', compact('menu'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $menu         = new Menu;
        $menu->title  = $data['title'];
        $menu->status = 1;
        $menu->save();

        return redirect()->route('menus.show', $menu)->with('status', $menu->title . ' oluşturuldu.');
    }

    public function show(Request $request, Menu $menu): View
    {
        $columns = collect($this->itemColumns($menu))->keyBy('type')->all();
        $items   = $menu->items()->get();
        $roots   = $items->where('main', 0)->where('parent_id', 0)->values();
        $tree    = $roots->map(fn (MenuItem $root) => ['item' => $root, 'children' => $items->where('parent_id', $root->id)->values()]);
        $placed  = $roots->pluck('id')->merge($tree->flatMap(fn (array $node) => $node['children']->pluck('id')));
        $fixed   = $items->reject(fn (MenuItem $item) => $placed->contains($item->id))->values();

        return view('crudPackage::menus.detail', compact('menu', 'items', 'columns', 'tree', 'fixed'));
    }

    protected function itemColumns(Menu $menu): array
    {
        return [
            [
                'type'     => 'lead',
                'label'    => 'Öğe',
                'width'    => '1.6fr',
                'noAvatar' => true,
                'name'     => fn (MenuItem $item) => $item->title,
                'sub'      => fn (MenuItem $item) => $item->route,
            ],
            [
                'type'  => 'badge',
                'label' => 'Tür',
                'width' => '160px',
                'value' => fn (MenuItem $item) => $item->main == 1 ? 'Sistem' : ($item->special == 1 ? 'Özel link' : 'Link'),
                'tone'  => fn (MenuItem $item) => $item->main == 1 ? 'neutral' : ($item->special == 1 ? 'primary' : 'accent'),
            ],
            [
                'type'   => 'actions',
                'width'  => '60px',
                'status' => false,
                'menu'   => fn (MenuItem $item) => $item->main == 1 ? [] : Actions::menu('menus.items', $item,
                [
                    'editTitle' => 'Öğeyi düzenle',
                    'except'    => [],
                ]),
            ],
        ];
    }

    public function edit(Request $request, Menu $menu): View
    {
        return view($request->ajax() ? 'crudPackage::menus.form' : 'crudPackage::menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->validated($request);

        $menu->title = $data['title'];
        $menu->save();

        return back()->with('status', 'Menü güncellendi.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate(
        [
            'title' => ['required', 'string', 'max:255'],
        ],
        [],
        [
            'title' => 'Başlık',
        ]);
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        if ($this->locked($menu))
        {
            return back()->with('error', 'Panel menüsü silinemez.');
        }

        $menu->items()->delete();
        $menu->delete();

        return redirect()->route('menus.index')->with('status', $menu->title . ' silindi.');
    }
}

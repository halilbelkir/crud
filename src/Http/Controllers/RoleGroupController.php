<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use crudPackage\Models\MenuItem;
use crudPackage\Models\RoleGroup;
use crudPackage\Support\Actions;
use crudPackage\Support\Search;
use crudPackage\Support\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RoleGroupController extends Controller
{

    public $permissions =
        [
            1 =>
                [
                    'title'  => 'Listeleme',
                    'column' => 'browse'
                ],
            2 =>
                [
                    'title'  => 'Detay Görme',
                    'column' => 'read'
                ],
            3 =>
                [
                    'title'  => 'Düzenleme',
                    'column' => 'edit'
                ],
            4 =>
                [
                    'title'  => 'Ekleme',
                    'column' => 'add'
                ],
            5 =>
                [
                    'title'  => 'Silme',
                    'column' => 'delete'
                ]
        ];

    public function index(Request $request): View|Response
    {
        $records = $this->scoped($request)
            ->withCount('users')
            ->withCount(['roles as granted_count' => fn ($query) => $query->where('browse', 1)])
            ->when($request->filled('q'), function ($query) use ($request)
            {
                $query->where('title', 'like', Search::pattern((string) $request->string('q')));
            })
            ->orderBy('title');

        $columns = $this->columns();
        $records = Table::sort($records, $columns);

        if ($export = Table::export($records, $columns, 'Yetkiler'))
        {
            return $export;
        }

        $roleGroups = $records->paginate(Table::perPage())->withQueryString();
        $total      = $this->scoped($request)->count();
        $assigned   = $this->scoped($request)->has('users')->count();

        return view('crudPackage::roleGroups.index', compact('roleGroups', 'columns', 'total', 'assigned'));
    }

    protected function scoped(Request $request): Builder
    {
        return RoleGroup::query()->when($request->user()->role_group_id != 1, function ($query) use ($request)
        {
            $query->where('id', $request->user()->role_group_id);
        });
    }

    protected function columns(): array
    {
        return [
            [
                'type'     => 'lead',
                'label'    => 'Yetki',
                'width'    => '1.6fr',
                'sortable' => 'title',
                'name'     => fn (RoleGroup $roleGroup) => $roleGroup->title,
                'sub'      => fn (RoleGroup $roleGroup) => $this->admin($roleGroup) ? 'Tüm modüllere erişir' : null,
            ],
            [
                'type'     => 'text',
                'label'    => 'Kullanıcı',
                'width'    => '120px',
                'bold'     => true,
                'sortable' => 'users_count',
                'value'    => fn (RoleGroup $roleGroup) => $roleGroup->users_count,
            ],
            [
                'type'  => 'badge',
                'label' => 'Erişim',
                'width' => '180px',
                'value' => fn (RoleGroup $roleGroup) => $this->admin($roleGroup) ? 'Tam yetki' : $roleGroup->granted_count . ' modül',
                'tone'  => fn (RoleGroup $roleGroup) => $this->admin($roleGroup) ? 'primary' : ($roleGroup->granted_count > 0 ? 'accent' : 'neutral'),
            ],
            [
                'type'  => 'actions',
                'width' => '60px',
                'menu'  => fn (RoleGroup $roleGroup) => Actions::menu('role-groups', $roleGroup,
                [
                    'editTitle' => 'Yetkiyi düzenle',
                    'except'    => $this->admin($roleGroup) ? ['delete'] : [],
                ]),
            ],
        ];
    }

    protected function admin(RoleGroup $roleGroup): bool
    {
        return (int) $roleGroup->getKey() === 1;
    }

    public function create(Request $request): View
    {
        $roleGroup = new RoleGroup;
        $groups    = $this->groups($roleGroup);

        return view($request->ajax() ? 'crudPackage::roleGroups.form' : 'crudPackage::roleGroups.edit', compact('roleGroup', 'groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data === null)
        {
            return back()->withInput()->with('error', 'En az bir izin seçilmelidir.');
        }

        $roleGroup = DB::transaction(function () use ($data, $request)
        {
            $roleGroup        = new RoleGroup;
            $roleGroup->title = $data['title'];
            $roleGroup->save();

            $this->sync($roleGroup, $request);

            return $roleGroup;
        });

        return redirect()->route('role-groups.index')->with('status', $roleGroup->title . ' oluşturuldu.');
    }

    public function show(RoleGroup $roleGroup): View
    {
        $roleGroup->loadCount('users');

        $groups = $this->groups($roleGroup);

        return view('crudPackage::roleGroups.detail', compact('roleGroup', 'groups'));
    }

    public function edit(Request $request, RoleGroup $roleGroup): View
    {
        $groups = $this->groups($roleGroup);

        return view($request->ajax() ? 'crudPackage::roleGroups.form' : 'crudPackage::roleGroups.edit', compact('roleGroup', 'groups'));
    }

    public function update(Request $request, RoleGroup $roleGroup): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data === null)
        {
            return back()->withInput()->with('error', 'En az bir izin seçilmelidir.');
        }

        DB::transaction(function () use ($data, $request, $roleGroup)
        {
            $roleGroup->title = $data['title'];
            $roleGroup->save();

            $this->sync($roleGroup, $request);
        });

        return redirect()->route('role-groups.index')->with('status', 'Yetki güncellendi.');
    }

    public function destroy(RoleGroup $roleGroup): RedirectResponse
    {
        if ($this->admin($roleGroup))
        {
            return back()->with('error', 'Tam yetkili grup silinemez.');
        }

        if ($roleGroup->users()->exists())
        {
            return back()->with('error', 'Bu yetkiye bağlı kullanıcılar var. Kullanıcıların yetkisini değiştirip tekrar deneyin.');
        }

        DB::transaction(function () use ($roleGroup)
        {
            $roleGroup->roles()->delete();
            $roleGroup->delete();
        });

        return redirect()->route('role-groups.index')->with('status', $roleGroup->title . ' silindi.');
    }

    protected function validated(Request $request): ?array
    {
        $data = $request->validate(
        [
            'title'               => ['required', 'string', 'max:255'],
            'permissions'         => ['nullable', 'array'],
            'special_permissions' => ['nullable', 'array'],
        ],
        [],
        [
            'title' => 'Başlık',
        ]);

        if (empty($data['permissions']) && empty($data['special_permissions']))
        {
            return null;
        }

        return $data;
    }

    protected function sync(RoleGroup $roleGroup, Request $request): void
    {
        $cruds = (array) $request->input('permissions', []);
        $menus = (array) $request->input('special_permissions', []);

        foreach (Crud::where('status', 1)->get() as $crud)
        {
            $this->grant($roleGroup, ['crud_id' => $crud->id], (array) ($cruds[$crud->id] ?? []));
        }

        foreach (MenuItem::where('special', 1)->get() as $menu)
        {
            $this->grant($roleGroup, ['menu_item_id' => $menu->id], (array) ($menus[$menu->id] ?? []));
        }

        $roleGroup->roles()->whereNull('crud_id')->whereNull('menu_item_id')->delete();
    }

    protected function grant(RoleGroup $roleGroup, array $target, array $selected): void
    {
        $role = $roleGroup->roles()->where($target)->first() ?? $roleGroup->roles()->make()->forceFill($target);

        foreach ($this->permissions as $key => $permission)
        {
            $role->{$permission['column']} = isset($selected[$key]) ? 1 : 0;
        }

        $role->save();
    }

    protected function groups(RoleGroup $roleGroup): array
    {
        $scope  = (int) Auth::user()->role_group_id;
        $roles  = $roleGroup->exists ? $roleGroup->roles()->get() : collect();
        $groups = [];

        $cruds = Crud::where('status', 1)
            ->when($scope !== 1, fn ($query) => $query->withRolePermissions($scope))
            ->orderBy('main', 'desc')
            ->get();

        $menus = MenuItem::where('special', 1)
            ->when($scope !== 1, fn ($query) => $query->withSpecialItemRolePermissions($scope))
            ->orderBy('title')
            ->get();

        foreach ($cruds as $crud)
        {
            $role  = $roles->firstWhere('crud_id', $crud->id);
            $items = [];

            foreach ($this->permissions as $key => $permission)
            {
                $items[] = [
                    'name'    => 'permissions[' . $crud->id . '][' . $key . ']',
                    'label'   => $permission['title'],
                    'checked' => (bool) $role?->{$permission['column']},
                ];
            }

            $groups[] = ['label' => $crud->title, 'items' => $items];
        }

        foreach ($menus as $menu)
        {
            $role = $roles->firstWhere('menu_item_id', $menu->id);

            $groups[] = [
                'label' => $menu->title,
                'items' => [
                    [
                        'name'    => 'special_permissions[' . $menu->id . '][1]',
                        'label'   => $this->permissions[1]['title'],
                        'checked' => (bool) $role?->browse,
                    ],
                ],
            ];
        }

        return $groups;
    }
}

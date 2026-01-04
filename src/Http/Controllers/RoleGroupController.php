<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use crudPackage\Models\MenuItem;
use crudPackage\Models\Role;
use crudPackage\Models\RoleGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mockery\Exception;
use Session;
use Validator;
use Yajra\DataTables\Facades\DataTables;

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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cruds        = Crud::where('status',1)->orderBy('main','desc')->get();
        $specialMenus = MenuItem::where('special',1)->orderBy('title','asc')->get();
        $permissions  = $this->permissions;
        $user         = Auth::user();

        if ($user->role_group_id != 1)
        {
            $cruds        = Crud::where('status',1)->withRolePermissions($user->role_group_id)->orderBy('main','desc')->get();
            $specialMenus = MenuItem::where('special',1)->withSpecialItemRolePermissions($user->role_group_id)->orderBy('title','asc')->get();
        }

        return view('crudPackage::roleGroups.index',compact('cruds','permissions','specialMenus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try
        {
            $attribute =
                [
                    'title'           => 'Başlık',
                    'permissions_all' => 'İzinler',
                ];

            $rules =
                [
                    'title'           => 'required',
                    'permissions_all' => 'required',
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


            $data         = new RoleGroup;
            $data->title  = $request->get('title');
            $data->save();

            $crudsSql           = Crud::where('status',1)->orderBy('main','desc')->get();
            $cruds              = $request->get('permissions');
            $specialPermissions = $request->get('special_permissions');
            $specialMenus       = MenuItem::where('special',1)->orderBy('title','asc')->get();

            foreach ($crudsSql as $key => $crud)
            {
                $roles                = new Role;
                $roles->role_group_id = $data->id;
                $roles->crud_id       = $crud->id;

                if (!isset($cruds[$crud->id]))
                {
                    foreach ($this->permissions as $permission)
                    {
                        $roles->{$permission['column']} = 0;
                    }
                }
                else
                {
                    foreach ($cruds[$crud->id] as $index => $permission)
                    {
                        $permission = (int) $permission;
                        $roles->{$this->permissions[$index]['column']} = $permission;
                    }
                }

                $roles->save();
            }

            foreach ($specialMenus as $specialMenuKey => $specialMenu)
            {
                $roles                = new Role;
                $roles->role_group_id = $data->id;
                $roles->menu_item_id  = $specialMenu->id;

                if (!isset($specialPermissions[$specialMenu->id]))
                {
                    foreach ($this->permissions as $permission)
                    {
                        $roles->{$permission['column']} = 0;
                    }
                }
                else
                {
                    foreach ($specialPermissions[$specialMenu->id] as $index => $permission)
                    {
                        $permission = (int) $permission;
                        $roles->{$this->permissions[$index]['column']} = $permission;
                    }
                }

                $roles->save();
            }

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                ]
            );

        }
        catch (Exception $e)
        {
            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RoleGroup $roleGroup)
    {
        $value        = $roleGroup;
        $cruds        = Crud::where('status',1)->orderBy('main','desc')->get();
        $specialMenus = MenuItem::where('special',1)->orderBy('title','asc')->get();
        $permissions  = $this->permissions;

        if (Auth::user()->role_group_id != 1)
        {
            $cruds        = Crud::where('status',1)->withRolePermissions($value->id)->orderBy('main','desc')->get();
            $specialMenus = MenuItem::where('special',1)->withSpecialItemRolePermissions($value->id)->orderBy('title','asc')->get();
        }

        return view('crudPackage::roleGroups.show',compact('cruds','permissions','value','specialMenus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoleGroup $roleGroup)
    {
        $value        = $roleGroup;
        $cruds        = Crud::where('status',1)->orderBy('main','desc')->get();
        $specialMenus = MenuItem::where('special',1)->orderBy('title','asc')->get();
        $permissions  = $this->permissions;

        if (Auth::user()->role_group_id != 1)
        {
            $cruds        = Crud::where('status',1)->withRolePermissions($value->id)->orderBy('main','desc')->get();
            $specialMenus = MenuItem::where('special',1)->withSpecialItemRolePermissions($value->id)->orderBy('title','asc')->get();
        }

        return view('crudPackage::roleGroups.edit',compact('cruds','permissions','value','specialMenus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoleGroup $roleGroup)
    {
        try
        {
            $attribute =
                [
                    'title'           => 'Başlık',
                    'permissions_all' => 'İzinler',
                ];

            $rules =
                [
                    'title'           => 'required',
                    'permissions_all' => 'required',
                ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attribute);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result' => 2,
                        'message' => $validator->errors()
                    ], 403
                );
            }


            $data        = $roleGroup;
            $data->title = $request->get('title');
            $data->save();

            $crudsSql           = Crud::where('status',1)->orderBy('main','desc')->get();
            $cruds              = $request->get('permissions');
            $specialPermissions = $request->get('special_permissions');
            $specialMenus       = MenuItem::where('special',1)->orderBy('title','asc')->get();

            foreach ($crudsSql as $key => $crud)
            {
                $roles = Role::where('role_group_id', $data->id)->where('crud_id', $crud->id)->first();

                if (!isset($cruds[$crud->id]))
                {
                    foreach ($this->permissions as $permission)
                    {
                        $roles->{$permission['column']} = 0;
                    }
                }
                else
                {
                    foreach ($this->permissions as $permissionKey => $permission)
                    {
                        if (isset($cruds[$crud->id][$permissionKey]))
                        {
                            $roles->{$permission['column']} = 1;
                        }
                        else
                        {
                            $roles->{$permission['column']} = 0;
                        }
                    }
                }

                $roles->save();
            }

            foreach ($specialMenus as $specialMenuKey => $specialMenu)
            {
                $roles = Role::where('role_group_id', $data->id)->where('menu_item_id', $specialMenu->id)->first();

                if (empty($roles))
                {
                    $roles                = new Role;
                    $roles->role_group_id = $data->id;
                    $roles->menu_item_id  = $specialMenu->id;
                }

                if (!isset($specialPermissions[$specialMenu->id]))
                {
                    foreach ($this->permissions as $permission)
                    {
                        $roles->{$permission['column']} = 0;
                    }
                }
                else
                {
                    foreach ($this->permissions as $permissionKey => $permission)
                    {
                        if (isset($specialPermissions[$specialMenu->id][$permissionKey]))
                        {
                            $roles->{$permission['column']} = 1;
                        }
                        else
                        {
                            $roles->{$permission['column']} = 0;
                        }
                    }
                }

                $roles->save();
            }

            return response()->json(
                [
                    'result' => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => $request->has('other_route') ? $request->get('other_route') : route('role-groups.index')
                ]
            );
        }
        catch (Exception $e)
        {
            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoleGroup $roleGroup)
    {
        try
        {
            if ($roleGroup->users()->count() > 0)
            {
                return response()->json(
                    [
                        'result'  => 0,
                        'message' => 'Bu yetkiye ait kullanıcılar tespit edildi.Lütfen kullanıcıların yetkilerini kaldırınız ve tekrar deneyiniz.'
                    ],403);
            }
            else
            {
                $roleGroup->roles()->delete();
                $roleGroup->delete();
            }

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route('role-groups.index')
                ]
            );
        }
        catch (Exception $e)
        {
            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }

    public function datatable()
    {
        $roles = RoleGroup::all();
        $user  = Auth::user();

        if ($user->role_group_id != 1)
        {
            $roles = RoleGroup::where('id',$user->role_group_id)->get();
        }

        return Datatables::of($roles)
            ->addColumn('actions', function ($value)
            {
                if (auth()->user()->hasPermission('role-groups.destroy') || auth()->user()->hasPermission('role-groups.edit') || auth()->user()->hasPermission('role-groups.show'))
                {
                    $actions  = '<a href="#" class="btn btn-sm btn-light btn-active-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-target="action-'.$value->id.'"> Aksiyon <i class="ki-duotone ki-down fs-5 ms-1"></i> </a>';
                    $actions .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-primary fw-semibold fs-7 w-125px py-4"  data-kt-menu="true" id="action-'.$value->id.'">';

                    if (auth()->user()->hasPermission('role-groups.show'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="' . route('role-groups.show', $value->id) . '" class="menu-link px-3"> Detay </a> </div>';
                    }

                    if (auth()->user()->hasPermission('role-groups.edit'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="'.route('role-groups.edit',$value->id).'" class="menu-link px-3"> Düzenle </a> </div>';
                    }

                    if (auth()->user()->hasPermission('role-groups.edit'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-model-name="RoleGroup" data-status="'.$value->status.'" data-id="'.$value->id.'" data-route="'.route('statusUpdate').'" class="menu-link px-3" onclick="statusUpdate(this)"> '.($value->status == 0 ? 'Aktif Et' : 'Pasif Et').' </a> </div>';                }

                    if (auth()->user()->hasPermission('role-groups.destroy'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-title="'.$value->title.' isimli rolü" data-route="'.route('role-groups.destroy',$value->id).'" class="menu-link px-3" onclick="destroy(this)"> Sil </a> </div>';
                    }

                    $actions .= '</div>';
                }
                else
                {
                    $actions = '';
                }

                return $actions;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }
}

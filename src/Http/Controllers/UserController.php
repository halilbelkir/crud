<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Customer;
use crudPackage\Models\CustomerLimits;
use crudPackage\Models\RoleGroup;
use crudPackage\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mockery\Exception;
use Session;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = RoleGroup::all();

        return view('crudPackage::users.index',compact('roles'));
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
                    'name'          => 'Ad & Soyad',
                    'email'         => 'E-mail',
                    'role_group_id' => 'Yetki',
                ];

            $rules =
                [
                    'name'          => 'required',
                    'email'         => 'required|email',
                    'role_group_id' => 'required',
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

            $newPassword          = 'Zaurac12345.,';
            $data                 = new User;
            $data->name           = $request->get('name');
            $data->email          = $request->get('email');
            $data->role_group_id  = $request->get('role_group_id');
            $data->password       = Hash::make($newPassword);
            $data->save();

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
    public function show(User $user)
    {
        $value = $user;

        return view('crudPackage::users.show',compact('value'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $value = $user;
        $roles = RoleGroup::all();

        return view('crudPackage::users.edit',compact('value','roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try
        {
            $attribute =
                [
                    'name'          => 'Ad & Soyad',
                    'email'         => 'E-mail',
                    'role_group_id' => 'Yetki',
                ];

            $rules =
                [
                    'name'          => 'required',
                    'email'         => 'required|email',
                    'role_group_id' => 'required',
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

            $data                = $user;
            $data->name          = $request->get('name');
            $data->email         = $request->get('email');
            $data->role_group_id = $request->get('role_group_id');
            $data->save();


            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => $request->has('other_route') ? $request->get('other_route') : route('users.index')
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
    public function destroy(User $user)
    {
        try
        {
            $user->delete();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route('users.index')
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
        return Datatables::of(User::where('id','!=',Auth::user()->id)->get())
            ->addColumn('role_group', function ($value) {
                return $value->roleGroup->title ?? '-';
            })
            ->editColumn('status', function ($value) {
                return $value->status == 1 ? '<span class="badge badge-success">Aktif</span>' :  '<span class="badge badge-danger">Pasif</span>';
            })
            ->addColumn('actions', function ($value)
            {
                if (auth()->user()->hasPermission('users.destroy') || auth()->user()->hasPermission('users.edit') || auth()->user()->hasPermission('users.show'))
                {
                    $actions  = '<a href="#" class="btn btn-sm btn-light btn-active-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-target="action-'.$value->id.'"> Aksiyon <i class="ki-duotone ki-down fs-5 ms-1"></i> </a>';
                    $actions .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-primary fw-semibold fs-7 w-125px py-4"  data-kt-menu="true" id="action-'.$value->id.'">';

                    if (auth()->user()->hasPermission('users.edit'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="'.route('users.edit',$value->id).'" class="menu-link px-3"> Düzenle </a> </div>';
                    }

                    if (auth()->user()->hasPermission('users.edit'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-model-name="User" data-status="'.$value->status.'" data-id="'.$value->id.'" data-route="'.route('statusUpdate').'" class="menu-link px-3" onclick="statusUpdate(this)"> '.($value->status == 0 ? 'Aktif Et' : 'Pasif Et').' </a> </div>';                }

                    if (auth()->user()->hasPermission('users.destroy'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-title="'.$value->title.' isimli kullanıcıyı" data-route="'.route('users.destroy',$value->id).'" class="menu-link px-3" onclick="destroy(this)"> Sil </a> </div>';
                    }

                    $actions .= '</div>';
                }
                else
                {
                    $actions = '';
                }

                return $actions;
            })
            ->rawColumns(['actions','status','role_group'])
            ->toJson();
    }
}

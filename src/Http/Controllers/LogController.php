<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Http\Controllers\Controller;
use crudPackage\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('crudPackage::logs.index');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $value   = Activity::findOrFail($id);
        $status  = $value->description;
        $message = '';
        $values  = json_decode($value->properties);
        $diff    = null;
        $user    = User::find($value->causer_id);

        if ($status == 1)
        {
            $message = '<span class="badge badge-success">Ekleme Yapıldı.</span>';
        }
        else if ($status == 2)
        {
            $diff    = diffFields( (array)$values->old ?? [], (array)$values->new ?? [] );
            $message = '<span class="badge badge-warning">Düzenleme Yapıldı</span>';
        }
        else if ($status == 3)
        {
            $message = '<span class="badge badge-danger">Silme Yapıldı</span>';
        }

        return view('crudPackage::logs.show',compact('value','message','values','diff','user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function datatable()
    {
        return Datatables::of(Activity::all())
            ->editColumn('description', function ($value) 
            {
                $status  = $value->description;
                $message = '';
                
                if ($status == 1)
                {
                    $message = '<span class="badge badge-success">Ekleme Yapıldı.</span>';
                }
                else if ($status == 2)
                {
                    $message = '<span class="badge badge-warning">Düzenleme Yapıldı</span>';
                }
                else if ($status == 3)
                {
                    $message = '<span class="badge badge-danger">Silme Yapıldı</span>';
                }
                
                return $message;
            })
            ->editColumn('created_at', function ($value)
            {
                return Carbon::make($value->created_at)->format('d-m-Y H:i:s');
            })
            ->editColumn('user', function ($value)
            {
                $user    = User::find($value->causer_id);

                return $user->name;
            })
            ->addColumn('actions', function ($value)
            {
                if (auth()->user()->hasPermission('logs.show'))
                {
                    $actions  = '<a href="#" class="btn btn-sm btn-light btn-active-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-target="action-'.$value->id.'"> Aksiyon <i class="ki-duotone ki-down fs-5 ms-1"></i> </a>';
                    $actions .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-primary fw-semibold fs-7 w-125px py-4"  data-kt-menu="true" id="action-'.$value->id.'">';

                    if (auth()->user()->hasPermission('logs.show'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="' . route('logs.show', $value->id) . '" class="menu-link px-3"> Detay </a> </div>';
                    }

                    $actions .= '</div>';
                }
                else
                {
                    $actions = '';
                }

                return $actions;
            })
            ->rawColumns(['actions','description','created_at','user'])
            ->toJson();
    }
}

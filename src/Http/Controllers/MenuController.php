<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use crudPackage\Models\Menu;
use crudPackage\Models\MenuItem;
use Illuminate\Http\Request;
use Mockery\Exception;
use Session;
use Validator;
use Yajra\DataTables\Facades\DataTables;





class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cruds = Crud::where('status',1)->get();

        return view('crudPackage::menus.index',compact('cruds'));
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
                    'title'         => 'Başlık',
                    'items'         => 'Menü Linkleri',
                    'items.*.title' => 'Başlık',
                    'items.*.route' => 'Url / Route',
                ];

            $rules =
                [
                    'title'         => 'required',
                    'items'         => 'required|array|min:1',
                    'items.*.title' => 'required|string|max:255',
                    'items.*.route' => 'required|string|max:255',
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


            $data         = new Menu();
            $data->title  = $request->get('title');
            $data->save();

            $menuItems = $request->get('items');

            foreach ($menuItems as $key => $item)
            {
                $menuItem = new MenuItem();

                $this->setItem($menuItem,$item,$data->id,($key + 1));
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
    public function show(Menu $menu)
    {
        $value      = $menu;

        return view('crudPackage::menus.show',compact('value'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $value = $menu;
        $cruds = Crud::where('status',1)->get();

        return view('crudPackage::menus.edit',compact('cruds','value'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        try
        {
            $attribute =
                [
                    'title'         => 'Başlık',
                    'items'         => 'Menü Linkleri',
                    'items.*.title' => 'Başlık',
                    'items.*.route' => 'Url / Route',
                ];

            $rules =
                [
                    'title'         => 'required',
                    'items'         => 'required|array|min:1',
                    'items.*.title' => 'required|string|max:255',
                    'items.*.route' => 'required|string|max:255',
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


            $data         = $menu;
            $data->title  = $request->get('title');
            $data->save();

            $menuItemsSql = MenuItem::where('menu_id',$data->id)->orderBy('order','asc')->get();
            $menuItems    = $request->get('items');

            foreach ($menuItemsSql as $item)
            {
                $index = array_search($item->id, array_column($menuItems, "id"));

                if ($index !== false)
                {
                    $menuItem = $menuItems[$index];

                    $this->setItem($item,$menuItem,$data->id,($index + 1));

                    unset($menuItems[$index]);
                    $menuItems = array_values($menuItems);
                }
                else
                {
                    $item->delete();
                }
            }

            foreach ($menuItems as $item)
            {
                $menuItemLast = MenuItem::where('menu_id',$data->id)->orderBy('order','desc')->first();
                $menuItem     = new MenuItem();

                $this->setItem($menuItem,$item,$data->id,($menuItemLast->order + 1));
            }

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => $request->has('other_route') ? $request->get('other_route') : route('menus.index')
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

    public function setItem($menuItem,$item,$menuId,$order)
    {
        $menuItem->menu_id       = $menuId;
        $menuItem->order         = $order;
        $menuItem->title         = $item['title'];
        $menuItem->route         = $item['route'];
        $menuItem->icon          = $item['icon'];
        $menuItem->dynamic_route = isset($item['dynamic_route']) ? 1 : 0;
        $menuItem->target        = isset($item['target']) ? 1 : 0;
        $menuItem->save();

        return $menuItem;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function datatable()
    {
        return Datatables::of(Menu::all())
            ->editColumn('status', function ($value) {
                return $value->status == 1 ? '<span class="badge badge-success">Aktif</span>' :  '<span class="badge badge-danger">Pasif</span>';
            })
            ->addColumn('actions', function ($value)
            {
                if (auth()->user()->hasPermission('menus.destroy') || auth()->user()->hasPermission('menus.edit') || auth()->user()->hasPermission('menus.show'))
                {
                    $actions  = '<a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-target="action-'.$value->id.'"> Aksiyon <i class="ki-duotone ki-down fs-5 ms-1"></i> </a>';
                    $actions .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"  data-kt-menu="true" id="action-'.$value->id.'">';

                    if (auth()->user()->hasPermission('menus.show'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="' . route('menus.show', $value->id) . '" class="menu-link px-3"> Detay </a> </div>';
                    }

                    if (auth()->user()->hasPermission('menus.edit'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="'.route('menus.edit',$value->id).'" class="menu-link px-3"> Düzenle </a> </div>';
                    }

                    if (auth()->user()->hasPermission('menus.edit')  && $value->id != 1)
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-model-name="Menu" data-status="'.$value->status.'" data-id="'.$value->id.'" data-route="'.route('statusUpdate').'" class="menu-link px-3" onclick="statusUpdate(this)"> '.($value->status == 0 ? 'Aktif Et' : 'Pasif Et').' </a> </div>';                }

                    if (auth()->user()->hasPermission('menus.destroy') && $value->id != 1)
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-title="'.$value->title.' isimli menüyü" data-route="'.route('menus.destroy',$value->id).'" class="menu-link px-3" onclick="destroy(this)"> Sil </a> </div>';
                    }

                    $actions .= '</div>';
                }
                else
                {
                    $actions = '';
                }

                return $actions;
            })
            ->rawColumns(['actions','status'])
            ->toJson();
    }

    public function crud(Request $request,Crud $crud)
    {
        try
        {
            $attribute =
                [
                    'id' => 'Ekranlar',
                ];

            $rules =
                [
                    'id' => 'required',
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

            return response()->json(
                [
                    'result'   => 1,
                    'response' => $crud,
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

    public function orderable(Request $request)
    {
        try
        {
            $attribute =
                [
                    'menus' => 'Menüler',
                ];

            $rules =
                [
                    'menus' => 'required',
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

            $data = $request->get('menus');

            foreach ($data as $order => $value)
            {
                $parent            = MenuItem::find($value['id']);
                $parent->order     = $order + 1;
                $parent->parent_id = 0;
                $parent->save();

                if (isset($value['children']) && count($value['children']) > 0)
                {
                    foreach ($value['children'] as $childOrder => $child)
                    {
                        $childMenu            = MenuItem::find($child['id']);
                        $childMenu->order     = $childOrder + 1;
                        $childMenu->parent_id = $parent->id;
                        $childMenu->save();
                    }
                }
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

    public function destroy(Menu $menu)
    {
        try
        {
            $menu->items()->delete();
            $menu->delete();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route('menus.index')
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
}

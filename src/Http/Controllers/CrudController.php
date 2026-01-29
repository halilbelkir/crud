<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Library\Relationships\CrudRelationships;
use crudPackage\Models\Crud;
use crudPackage\Models\CrudItem;
use crudPackage\Models\FormType;
use crudPackage\Models\MenuItem;
use crudPackage\Models\Role;
use crudPackage\Models\RoleGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery\Exception;
use Session;
use Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
class CrudController extends Controller
{
    public $visibility =
        [
            'required' => "Zorunlu Alan",
            'browse'   => "Listeme",
            'add'      => "Ekleme",
            'edit'     => "Düzenleme",
            'read'     => "Detay",
        ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('crudPackage::cruds.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($tableName)
    {
        $title        = Str::headline($tableName);
        $slug         = Str::slug($title);
        $singular     = Str::singular($tableName);
        $modelName    = Str::studly($singular);
        $modelName    = 'App\\Models\\' . $modelName;
        $columns      = Schema::getConnection()->getSchemaBuilder()->getColumns($tableName);
        $columns      = json_decode(json_encode($columns));
        $formTypes    = FormType::orderBy('title','desc')->get();
        $visibilities = $this->visibility;

        return view('crudPackage::cruds.create',compact('tableName','title','modelName','columns','formTypes','visibilities','slug'));
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
                    'title'                  => 'Menü Başlık',
                    'table_name'             => 'Tablo Adı',
                    'slug'                   => 'Link',
                    'display_single'         => 'Tekli Sayfa Başlık',
                    'display_plural'         => 'Çoklu Sayfa Başlık',
                    'model'                  => 'Model',
                    'columns'                => 'Modül Alanları',
                    'columns.*.column_name'  => 'Alan Adı',
                    'columns.*.form_type_id' => 'Tip',
                ];

            $rules =
                [
                    'title'                  => 'required',
                    'table_name'             => 'required',
                    'slug'                   => 'required|string|unique:cruds,slug',
                    'display_single'         => 'required',
                    'display_plural'         => 'required',
                    'columns'                => 'required|array|min:1',
                    'columns.*.column_name'  => 'required',
                    'columns.*.form_type_id' => 'required',
                    'model'                  => [
                                                    'required',
                                                    function ($attribute, $value, $fail)
                                                    {
                                                        if (!class_exists($value))
                                                        {
                                                            return $fail("Geçersiz model: {$value}");
                                                        }

                                                        if (!is_subclass_of($value, Model::class))
                                                        {
                                                            return $fail("{$value} bir Eloquent Model değil.");
                                                        }

                                                        if (!str_starts_with($value, 'App\\Models\\'))
                                                        {
                                                            return $fail("Model sadece App\\Models altında olmalı.");
                                                        }
                                                    }
                                                ]
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

            if ($request->has('order_column_name'))
            {
                $area1 =
                    [
                        'order_column_name' => $request->get('order_column_name'),
                        'order_direction'   => $request->get('order_direction'),
                    ];
            }

            $data                  = new Crud();
            $data->title           = $request->get('title');
            $data->slug            = $request->get('slug');
            $data->display_single  = $request->get('display_single');
            $data->display_plural  = $request->get('display_plural');
            $data->model           = $request->get('model');
            $data->content         = $request->get('content');
            $data->icon            = $request->get('icon');
            $data->table_name      = $request->get('table_name');
            $data->only_edit       = $request->has('only_edit') ? 1 : 0;
            $data->area_1          = isset($area1) ? json_encode($area1) : null;
            $data->save();

            $crudItems = $request->get('columns');

            foreach ($crudItems as $key => $item)
            {
                $crudItem = new CrudItem();

                $this->setItem($crudItem,$item,$data->id,($key + 1));
            }

            if ($data->only_edit == 1)
            {
                $newModuleData = new $data->model();
                $newModuleData->save();
                $route = $data->slug.'.edit,'.$newModuleData->id;
            }

            $menuId                  = 1;
            $menuItemLast            = MenuItem::where('menu_id',$menuId)->orderBy('order','desc')->first();
            $menuItem                = new MenuItem();
            $menuItem->menu_id       = $menuId;
            $menuItem->order         = $menuItemLast->order + 1;
            $menuItem->title         = $data->title;
            $menuItem->route         = $data->only_edit == 1 ? $route : $data->slug.'.index';
            $menuItem->icon          = $data->icon;
            $menuItem->dynamic_route = 1;
            $menuItem->target        = 0;
            $menuItem->save();

            $roleGroups = RoleGroup::get();

            foreach ($roleGroups as $roleGroup)
            {
                $roles                = new Role;
                $roles->role_group_id = $roleGroup->id;
                $roles->crud_id       = $data->id;
                $roles->browse        = 1;
                $roles->read          = 1;
                $roles->edit          = 1;
                $roles->add           = 1;
                $roles->delete        = 1;
                $roles->save();
            }

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => $request->has('other_route') ? $request->get('other_route') : route('cruds.index')
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

    public function setItem($crudItem,$item,$crudId,$order)
    {
        $crudItem->crud_id      = $crudId;
        $crudItem->order        = $order;
        $crudItem->title        = $item['title'];
        $crudItem->column_name  = $item['column_name'];
        $crudItem->detail       = $item['detail'] ?? '{}';
        $crudItem->form_type_id = $item['form_type_id'];
        $crudItem->required     = isset($item['required']) ? 1 : 0;
        $crudItem->browse       = isset($item['browse']) ? 1 : 0;
        $crudItem->read         = isset($item['read']) ? 1 : 0;
        $crudItem->edit         = isset($item['edit']) ? 1 : 0;
        $crudItem->add          = isset($item['add']) ? 1 : 0;
        $crudItem->delete       = isset($item['delete']) ? 1 : 0;
        $crudItem->save();

        return $crudItem;
    }

    /**
     * Display the specified resource.
     */
    public function show(Crud $crud)
    {
        $value        = $crud;
        $formTypes    = FormType::orderBy('title','desc')->get();
        $columns      = Schema::getConnection()->getSchemaBuilder()->getColumns($value->table_name);
        $columns      = json_decode(json_encode($columns));
        $visibilities = $this->visibility;

        return view('crudPackage::cruds.show',compact('formTypes','visibilities','value','columns'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Crud $crud)
    {
        $value        = $crud;
        $formTypes    = FormType::orderBy('title','desc')->get();
        $columns      = Schema::getConnection()->getSchemaBuilder()->getColumns($value->table_name);
        $columns      = json_decode(json_encode($columns));
        $visibilities = $this->visibility;
        $tables       = $this->getTablesSql(1);
        $crudItems    = CrudItem::where('crud_id',$crud->id)->orderBy('order','asc')->get();
        $area1        = isset($value->area_1) ? json_decode($value->area_1) : null;
        $newColumns   = [];

        foreach ($crudItems as $crudItem)
        {
            $newColumns[$crudItem->column_name]['item'] = $crudItem;

            if($crudItem->relationship == 1)
            {
                $detail = json_decode($crudItem->detail);

                if ($detail->type == 'belongsToMany')
                {
                    $newColumns[$crudItem->column_name]['attribute'] = (object)
                    [
                        "name"      => $crudItem->column_name,
                        "type_name" => "bigint",
                        "type"      => "bigint",
                        "nullable"  => false,
                    ];
                }
            }
        }

        foreach ($columns as $column)
        {
            $newColumns[$column->name]['attribute'] = $column;
        }

        return view('crudPackage::cruds.edit',compact('formTypes','visibilities','value','newColumns','tables','columns','area1'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Crud $crud)
    {
        try
        {
            $attribute =
                [
                    'title'                  => 'Menü Başlık',
                    'table_name'             => 'Tablo Adı',
                    'slug'                   => 'Link',
                    'display_single'         => 'Tekli Sayfa Başlık',
                    'display_plural'         => 'Çoklu Sayfa Başlık',
                    'model'                  => 'Model',
                    'columns'                => 'Modül Alanları',
                    'columns.*.column_name'  => 'Alan Adı',
                    'columns.*.form_type_id' => 'Tip',
                ];

            $rules =
                [
                    'title'                  => 'required',
                    'table_name'             => 'required',
                    'slug'                   => 'required|string|unique:cruds,slug,' . $crud->id,
                    'display_single'         => 'required',
                    'display_plural'         => 'required',
                    'columns'                => 'required|array|min:1',
                    'columns.*.column_name'  => 'required',
                    'columns.*.form_type_id' => 'required',
                    'model'                  => [
                        'required',
                        function ($attribute, $value, $fail)
                        {
                            if (!class_exists($value))
                            {
                                return $fail("Geçersiz model: {$value}");
                            }

                            if (!is_subclass_of($value, Model::class))
                            {
                                return $fail("{$value} bir Eloquent Model değil.");
                            }

                            if (!str_starts_with($value, 'App\\Models\\'))
                            {
                                return $fail("Model sadece App\\Models altında olmalı.");
                            }
                        }
                    ]
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

            if ($request->has('order_column_name'))
            {
                $area1 =
                    [
                        'order_column_name' => $request->get('order_column_name'),
                        'order_direction'   => $request->get('order_direction'),
                    ];
            }

            $data                  = $crud;
            $data->title           = $request->get('title');
            $data->slug            = $request->get('slug');
            $data->display_single  = $request->get('display_single');
            $data->display_plural  = $request->get('display_plural');
            $data->model           = $request->get('model');
            $data->content         = $request->get('content');
            $data->icon            = $request->get('icon');
            $data->only_edit       = $request->has('only_edit') ? 1 : 0;
            $data->area_1          = isset($area1) ? json_encode($area1) : null;
            $data->save();

            $crudItems   = $request->get('columns');
            $moduleModel = $data->model;
            $moduleFirst = $moduleModel::first();
            $route       = $data->slug.'.index';

            foreach ($crudItems as $crudItemKey => $item)
            {
                if (!empty($item['id']))
                {
                    $crudItem = CrudItem::find($item['id']);
                }
                else
                {
                    $crudItem = new CrudItem();
                }

                $this->setItem($crudItem,$item,$data->id,($crudItemKey + 1));
            }

            if ($data->only_edit == 1)
            {
                if ($moduleFirst)
                {
                    $route = $data->slug.'.edit,'.$moduleFirst->id;
                }
                else
                {
                    $newModuleData = new $moduleModel();
                    $newModuleData->save();
                    $route = $data->slug.'.edit,'.$newModuleData->id;
                }
            }

            $previousData            = $data->getPrevious()['slug'] ?? $data->slug;
            $menuItem                = MenuItem::whereLike('route','%'.$previousData.'%')->where('menu_id',1)->first();
            $menuItem->title         = $data->title;
            $menuItem->route         = $route;
            $menuItem->icon          = $data->icon;
            $menuItem->dynamic_route = isset($data->only_edit) ? 1 : $menuItem->dynamic_route;
            $menuItem->save();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => $request->has('other_route') ? $request->get('other_route') : route('cruds.index')
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
    public function destroy(Crud $crud)
    {
        try
        {
            $crud->menuItems()->delete();
            $crud->getRoles()->delete();
            $crud->items()->delete();
            $crud->delete();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route('cruds.index')
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
        $sql = $this->getTablesSql(1);

        return Datatables::of($sql)
            ->editColumn('status', function ($value)
            {
                if (isset($value->status))
                {
                    return $value->status == 1 ? '<span class="badge badge-success">Aktif</span>' :  '<span class="badge badge-danger">Pasif</span>';
                }

                return null;
            })
            ->addColumn('actions', function ($value)
            {
                if (empty($value->id) && auth()->user()->hasPermission('cruds.create'))
                {
                    $actions = '<a class="btn btn-primary btn-sm" href="'. route('cruds.create.new',$value->title).'">Oluştur</a>';
                }
                else if (auth()->user()->hasPermission('cruds.destroy') || auth()->user()->hasPermission('cruds.edit') || auth()->user()->hasPermission('cruds.show'))
                {
                    $actions  = '<a href="#" class="btn btn-sm btn-light btn-active-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-target="action-'.$value->id.'"> Aksiyon <i class="ki-duotone ki-down fs-5 ms-1"></i> </a>';
                    $actions .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-primary fw-semibold fs-7 w-125px py-4"  data-kt-menu="true" id="action-'.$value->id.'">';

                    if (auth()->user()->hasPermission('cruds.show'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="' . route('cruds.show', $value->id) . '" class="menu-link px-3"> Detay </a> </div>';
                    }

                    if (auth()->user()->hasPermission('cruds.edit'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="'.route('cruds.edit',$value->id).'" class="menu-link px-3"> Düzenle </a> </div>';
                    }

                    if (auth()->user()->hasPermission('cruds.edit')  && $value->id != 1)
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-model-name="Crud" data-status="'.$value->status.'" data-id="'.$value->id.'" data-route="'.route('statusUpdate').'" class="menu-link px-3" onclick="statusUpdate(this)"> '.($value->status == 0 ? 'Aktif Et' : 'Pasif Et').' </a> </div>';                }

                    if (auth()->user()->hasPermission('cruds.destroy') && $value->id != 1)
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-title="'.$value->title.' isimli modülü" data-route="'.route('cruds.destroy',$value->id).'" class="menu-link px-3" onclick="destroy(this)"> Sil </a> </div>';
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

    public function orderable(Request $request)
    {
        try
        {
            $attribute =
                [
                    'columns' => 'Alanlar',
                ];

            $rules =
                [
                    'columns' => 'required',
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

            $data = $request->get('columns');

            foreach ($data as $order => $value)
            {
                $parent            = CrudItem::find($value['id']);
                $parent->order     = $order;
                $parent->save();
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

    public function getColumns(Request $request)
    {
        try
        {
            $columns = Schema::getConnection()->getSchemaBuilder()->getColumns($request->get('table_name'));
            $columns = json_decode(json_encode($columns));

            return response()->json(
                [
                    'result'   => 1,
                    'response' => $columns,
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

    public function getTablesSql($status,$tableName = null)
    {
        $disabledTables = ['cruds','crud_items','form_types','menus','menu_items','migrations','users','roles','role_groups'];
        $disabledSql    = $status == 1 ? "and TABLE_NAME NOT IN ('".implode("','", $disabledTables)."')" : "and TABLE_NAME NOT IN ('".$tableName."','cruds','crud_items')";
        $sql            = "SELECT  t.table_name as title,c.id,c.status
                            FROM (
                                    SELECT TABLE_NAME as table_name 
                                    FROM INFORMATION_SCHEMA.TABLES 
                                    WHERE TABLE_SCHEMA = DATABASE()
                                    AND TABLE_TYPE = 'BASE TABLE'
                                    ".$disabledSql."
                                ) t
                            LEFT JOIN cruds c ON t.table_name = c.table_name";

        return DB::select($sql);
    }

    public function relationshipStore(Request $request,Crud $crud)
    {
        try
        {
            $attribute =
                [
                    'relationship_title'             => 'Görünecek İsim',
                    'relationship'                   => 'İlişki',
                    'relationship_column_name'       => 'Referans Alınacak Alan',
                    'relationship_table_name'        => 'Referans Tablo',
                    'relationship_pivot_table_name'  => 'Pivot Tablo',
                    'relationship_table_model'       => 'Referans Tablo Model',
                    'show_column'                    => 'Görüntülenecek Alan',
                    'match_column'                   => 'Eşleşecek Alan',
                ];

            $rules =
                [
                    'relationship_title'             => 'required',
                    'relationship'                   => 'required',
                    'relationship_table_name'        => 'required',
                    'relationship_column_name'       => [
                        Rule::requiredIf(
                            fn () => $request->relationship !== 'belongsToMany'
                        ),
                    ],
                    'relationship_pivot_table_name'  => [
                        Rule::requiredIf(
                            fn () => $request->relationship === 'belongsToMany'
                        ),
                    ],
                    'show_column'                    => 'required',
                    'match_column'                   => 'required',
                    'relationship_table_model'       => [
                        'required',
                        function ($attribute, $value, $fail)
                        {
                            if (!class_exists($value))
                            {
                                return $fail("Geçersiz model: {$value}");
                            }

                            if (!is_subclass_of($value, Model::class))
                            {
                                return $fail("{$value} bir Eloquent Model değil.");
                            }

                            if (!str_starts_with($value, 'App\\Models\\'))
                            {
                                return $fail("Model sadece App\\Models altında olmalı.");
                            }
                        }
                    ]
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

            $detail =
                [
                    'type'           => $request->get('relationship'),
                    'table_name'     => $request->get('relationship_table_name'),
                    'model'          => $request->get('relationship_table_model'),
                    'column_name'    => $request->get('relationship_column_name') ?? null,
                    'pivot_table'    => $request->get('relationship_pivot_table_name') ?? null,
                    'show_column'    => $request->get('show_column'),
                    'match_column'   => $request->get('match_column'),
                    'multiple'       => $request->has('relationship_pivot_table_name') ? true : false,
                ];

            if ($request->has('relationship_pivot_table_name'))
            {
                $columnName = CrudRelationships::generateColumnName($request->get('relationship_table_model'),$request->get('relationship_pivot_table_name'));
                $detailMany =
                    [
                        'related_key' => Str::singular($request->get('relationship_table_name')) . '_id',
                        'foreign_key' => Str::singular($crud->table_name) . '_id',
                        'column_name' => $columnName
                    ];

                $detail                = array_merge($detail, $detailMany);
                $crudItem              = CrudItem::where('column_name',$columnName)->where('crud_id',$crud->id)->first() ?? new CrudItem();
                $crudItem->column_name = $columnName;
                $crudItem->crud_id     = $crud->id;
            }
            else
            {
                $crudItem               = CrudItem::where('column_name',$request->get('relationship_column_name'))->where('crud_id',$crud->id)->first();
                $crudItem->column_name  = $request->get('relationship_column_name');
            }

            $crudItem->title        = $request->get('relationship_title');
            $crudItem->detail       = json_encode($detail);
            $crudItem->form_type_id = 16;
            $crudItem->relationship = 1;
            $crudItem->save();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.'
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

    public function relationshipDestroy(CrudItem $crudItem)
    {
        try
        {
            $details = json_decode($crudItem->detail);

            if ($details->type === 'belongsToMany')
            {
                $crudItem->delete();
            }
            else
            {
                $crudItem->relationship = 0;
                $crudItem->detail       = '{}';
                $crudItem->save();
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

    public function repeaterDestroy(CrudItem $crudItem)
    {
        try
        {
            $crudItem->repeater = 0;
            $crudItem->detail   = '{}';
            $crudItem->save();

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

    public function repeaterStore(Request $request,Crud $crud)
    {
        try
        {
            $attribute =
                [
                    'repeater_column_name'        => 'Referans Alınacak Alan',
                    'repeaterArea'                => 'Tekrarlanan Alanlar',
                    'repeaterArea.*.form_type_id' => 'Tip',
                    'repeaterArea.*.area_info'    => 'Alan Bilgileri',
                ];

            $rules =
                [
                    'repeater_column_name'        => 'required',
                    'repeaterArea'                => 'required|array|min:1',
                    'repeaterArea.*.form_type_id' => 'required|integer',
                    'repeaterArea.*.area_info'    => 'required|json',
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

            $repeaterAreas = $request->get('repeaterArea');
            $repeaterData  = [];

            foreach ($repeaterAreas as $repeaterArea)
            {
                $areaInfo                 = $repeaterArea['area_info'];
                $areaInfo                 = json_decode($areaInfo, true);
                $areaInfo['form_type_id'] = $repeaterArea['form_type_id'];
                array_push($repeaterData, $areaInfo);
            }

            $crudItem               = CrudItem::where('column_name',$request->get('repeater_column_name'))->first();
            $crudItem->column_name  = $request->get('repeater_column_name');
            $crudItem->detail       = json_encode($repeaterData);
            $crudItem->form_type_id = 17;
            $crudItem->repeater     = 1;
            $crudItem->save();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.'
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

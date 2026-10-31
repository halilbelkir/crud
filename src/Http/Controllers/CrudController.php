<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Library\Relationships\CrudRelationships;
use crudPackage\Models\Crud;
use crudPackage\Models\CrudItem;
use crudPackage\Models\FormType;
use crudPackage\Models\MenuItem;
use crudPackage\Models\Role;
use crudPackage\Models\RoleGroup;
use crudPackage\Support\Actions;
use crudPackage\Support\Menu as MenuSupport;
use crudPackage\Support\Permissions;
use crudPackage\Support\Search;
use crudPackage\Support\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
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

    protected $lockedColumns =
        [
            'id'         => 'hidden',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

    public function index(Request $request): View|Response
    {
        $cruds   = Crud::withCount('items')->get()->keyBy('table_name');
        $columns = $this->columns();
        $term    = Search::fold((string) $request->string('q'));
        $records = collect($this->getTablesSql(1))
            ->map(fn ($table) => $cruds[$table->title] ?? (new Crud())->forceFill(['table_name' => $table->title, 'title' => Str::headline($table->title)]))
            ->when($term !== '', fn ($rows) => $rows->filter(fn (Crud $crud) => str_contains(Search::fold($crud->title . ' ' . $crud->table_name), $term)))
            ->sortBy(fn (Crud $crud) => ($crud->exists ? '0' : '1') . $crud->table_name)
            ->values();

        if ($field = Table::sortColumn($columns))
        {
            $records = $records->sortBy($field, SORT_NATURAL | SORT_FLAG_CASE, Table::sortDirection() === 'desc')->values();
        }

        if ($export = Table::export($records, $columns, 'Modüller'))
        {
            return $export;
        }

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = Table::perPage();
        $rows    = new LengthAwarePaginator($records->forPage($page, $perPage)->values(), $records->count(), $perPage, $page,
        [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        $total   = $records->count();
        $created = $records->where('exists', true)->count();

        return view('crudPackage::cruds.index', compact('rows', 'columns', 'total', 'created'));
    }

    protected function columns(): array
    {
        return [
            [
                'type'     => 'lead',
                'label'    => 'Modül',
                'width'    => '1.6fr',
                'sortable' => 'title',
                'name'     => fn (Crud $crud) => $crud->title,
                'sub'      => fn (Crud $crud) => $crud->table_name,
            ],
            [
                'type'     => 'text',
                'label'    => 'Link',
                'width'    => '1fr',
                'sortable' => 'slug',
                'value'    => fn (Crud $crud) => $crud->exists ? '/' . $crud->slug : '—',
            ],
            [
                'type'     => 'text',
                'label'    => 'Alan',
                'width'    => '100px',
                'bold'     => true,
                'sortable' => 'items_count',
                'value'    => fn (Crud $crud) => $crud->exists ? $crud->items_count : '—',
            ],
            [
                'type'     => 'status',
                'label'    => 'Durum',
                'width'    => '150px',
                'sortable' => 'status',
                'value'    => fn (Crud $crud) => ! $crud->exists ? 'Kurulmadı' : ($crud->status == 1 ? 'Aktif' : 'Pasif'),
                'tone'     => fn (Crud $crud) => ! $crud->exists ? 'accent' : ($crud->status == 1 ? 'success' : 'muted'),
            ],
            [
                'type'   => 'actions',
                'width'  => '60px',
                'status' => fn (Crud $crud) => $crud->exists && $crud->main != 1,
                'menu'   => fn (Crud $crud) => $crud->exists
                    ? Actions::menu('cruds', $crud,
                    [
                        'drawer' => false,
                        'except' => $crud->main == 1 ? ['delete'] : [],
                    ])
                    : (Permissions::allows('cruds.create')
                        ? [['label' => 'Modül oluştur', 'icon' => 'plus', 'href' => route('cruds.create.new', $crud->table_name)]]
                        : []),
            ],
        ];
    }

    public function create($tableName): View
    {
        abort_unless(Schema::hasTable($tableName), 404);

        $title = Str::headline($tableName);
        $crud  = (new Crud())->forceFill(
        [
            'title'          => $title,
            'slug'           => Str::slug($title),
            'table_name'     => $tableName,
            'display_single' => Str::headline(Str::singular($tableName)),
            'display_plural' => $title,
            'model'          => 'App\\Models\\' . Str::studly(Str::singular($tableName)),
            'other_route'    => '{}',
            'translatable'   => 1,
        ]);

        return view('crudPackage::cruds.edit', $this->form($crud));
    }

    protected function form(Crud $crud): array
    {
        $columns = json_decode(json_encode(Schema::getConnection()->getSchemaBuilder()->getColumns($crud->table_name)));
        $items   = $crud->exists ? $crud->items()->orderBy('order')->get()->keyBy('column_name') : collect();
        $fields  = [];

        foreach ($items as $item)
        {
            $detail = json_decode($item->detail ?? '{}');

            if ($item->relationship == 1 && ($detail->type ?? null) === 'belongsToMany')
            {
                $fields[$item->column_name] = ['name' => $item->column_name, 'type' => 'bigint', 'nullable' => false, 'item' => $item];
            }
        }

        foreach ($columns as $column)
        {
            $fields[$column->name] = ['name' => $column->name, 'type' => $column->type_name, 'nullable' => $column->nullable, 'item' => $items[$column->name] ?? null];
        }

        uasort($fields, fn ($first, $second) => ($first['item']->order ?? PHP_INT_MAX) <=> ($second['item']->order ?? PHP_INT_MAX));

        return [
            'crud'         => $crud,
            'fields'       => array_values($fields),
            'columns'      => array_column($columns, 'name', 'name'),
            'formTypes'    => FormType::orderBy('title')->pluck('title', 'id')->all(),
            'formTypeKeys' => FormType::pluck('id', 'key')->all(),
            'visibilities' => $this->visibility,
            'locked'       => $this->lockedColumns,
            'icons'        => array_combine(MenuSupport::icons(), MenuSupport::icons()),
            'tables'       => array_column($this->getTablesSql(1), 'title', 'title'),
            'area'         => json_decode($crud->area_1 ?? '{}', true) ?? [],
        ];
    }

    protected function rules(?Crud $crud = null): array
    {
        return [
            'title'                  => 'required',
            'table_name'             => 'required',
            'slug'                   => 'required|string|unique:cruds,slug' . ($crud ? ',' . $crud->id : ''),
            'display_single'         => 'required',
            'display_plural'         => 'required',
            'icon'                   => ['nullable', Rule::in(MenuSupport::icons())],
            'other_route'            => 'nullable|json',
            'columns'                => 'required|array|min:1',
            'columns.*.column_name'  => 'required',
            'columns.*.form_type_id' => 'required',
            'columns.*.detail'       => 'nullable|json',
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
    }

    protected function attributes(): array
    {
        return [
            'title'                  => 'Menü Başlık',
            'table_name'             => 'Tablo Adı',
            'slug'                   => 'Link',
            'display_single'         => 'Tekli Sayfa Başlık',
            'display_plural'         => 'Çoklu Sayfa Başlık',
            'model'                  => 'Model',
            'icon'                   => 'İkon',
            'other_route'            => 'Diğer Route',
            'columns'                => 'Modül Alanları',
            'columns.*.column_name'  => 'Alan Adı',
            'columns.*.form_type_id' => 'Tip',
            'columns.*.detail'       => 'İsteğe Bağlı Ayrıntılar',
        ];
    }

    protected function fill(Crud $data, Request $request): Crud
    {
        $area1 = $request->filled('order_column_name')
            ? json_encode(['order_column_name' => $request->get('order_column_name'), 'order_direction' => $request->get('order_direction') ?: 'asc'])
            : null;

        $data->title          = $request->get('title');
        $data->slug           = $request->get('slug');
        $data->display_single = $request->get('display_single');
        $data->display_plural = $request->get('display_plural');
        $data->model          = $request->get('model');
        $data->content        = $request->get('content');
        $data->icon           = $request->get('icon');
        $data->other_route    = $request->get('other_route');
        $data->table_name     = $request->get('table_name');
        $data->only_edit      = $request->boolean('only_edit') ? 1 : 0;
        $data->translatable   = $request->boolean('translatable') ? 1 : 0;
        $data->area_1         = $area1;
        $data->save();

        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules(), [], $this->attributes());

        try
        {
            DB::beginTransaction();

            $data = $this->fill(new Crud(), $request);

            foreach (array_values($request->get('columns')) as $key => $item)
            {
                $this->setItem(new CrudItem(), $item, $data->id, ($key + 1));
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
            $menuItem->order         = ($menuItemLast->order ?? 0) + 1;
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

            DB::commit();

            return redirect()->route('cruds.show', $data)->with('status', $data->title . ' modülü oluşturuldu.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    public function setItem($crudItem,$item,$crudId,$order)
    {
        $crudItem->crud_id      = $crudId;
        $crudItem->order        = $order;
        $crudItem->title        = filled($item['title'] ?? null) ? $item['title'] : $item['column_name'];
        $crudItem->column_name  = $item['column_name'];
        $crudItem->detail       = filled($item['detail'] ?? null) ? $item['detail'] : '{}';
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

    public function show(Crud $crud): View
    {
        $items   = $crud->items()->with('type')->orderBy('order')->paginate(500)->withQueryString();
        $columns = $this->itemColumns();

        return view('crudPackage::cruds.detail', compact('crud', 'items', 'columns'));
    }

    protected function itemColumns(): array
    {
        return [
            [
                'type'     => 'lead',
                'label'    => 'Alan',
                'width'    => '1.4fr',
                'noAvatar' => true,
                'name'     => fn (CrudItem $item) => $item->title,
                'sub'      => fn (CrudItem $item) => $item->column_name,
            ],
            [
                'type'  => 'badge',
                'label' => 'Tip',
                'width' => '170px',
                'value' => fn (CrudItem $item) => $item->relationship == 1 ? 'İlişki' : ($item->type->title ?? '—'),
                'tone'  => fn (CrudItem $item) => $item->relationship == 1 || $item->repeater == 1 ? 'primary' : 'accent',
            ],
            [
                'type'  => 'chips',
                'label' => 'Görünürlük',
                'width' => '2fr',
                'items' => fn (CrudItem $item) => collect($this->visibility)
                    ->filter(fn ($label, $key) => $item->{$key} == 1)
                    ->map(fn ($label, $key) => ['label' => $label, 'tone' => $key === 'required' ? 'primary' : 'neutral'])
                    ->values()
                    ->all(),
            ],
        ];
    }

    public function edit(Crud $crud): View
    {
        return view('crudPackage::cruds.edit', $this->form($crud));
    }

    public function update(Request $request, Crud $crud): RedirectResponse
    {
        $request->merge(['table_name' => $crud->table_name]);
        $request->validate($this->rules($crud), [], $this->attributes());

        try
        {
            DB::beginTransaction();

            $data        = $this->fill($crud, $request);
            $moduleModel = $data->model;
            $moduleFirst = $moduleModel::first();
            $route       = $data->slug.'.index';

            foreach (array_values($request->get('columns')) as $crudItemKey => $item)
            {
                $crudItem = (!empty($item['id']) ? $crud->items()->find($item['id']) : null) ?? new CrudItem();

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

            $previousData = $data->getPrevious()['slug'] ?? $data->slug;
            $menuItem = MenuItem::where('menu_id', 1)
                ->where(function($query) use ($previousData) {
                    $query->where('route', $previousData . '.index')
                        ->orWhere('route', 'LIKE', $previousData . '.index,%')
                        ->orWhere('route', 'LIKE', $previousData . '.edit,%');
                })
                ->first();

            if ($menuItem)
            {
                $menuItem->title         = $data->title;
                $menuItem->route         = $route;
                $menuItem->icon          = $data->icon;
                $menuItem->dynamic_route = isset($data->only_edit) ? 1 : $menuItem->dynamic_route;
                $menuItem->save();
            }

            DB::commit();

            return redirect()->route('cruds.show', $data)->with('status', $data->title . ' modülü güncellendi.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    public function destroy(Crud $crud): RedirectResponse
    {
        if ($crud->main == 1)
        {
            return back()->with('error', 'Sistem modülleri silinemez.');
        }

        try
        {
            DB::transaction(function () use ($crud)
            {
                $crud->menuItems()->delete();
                $crud->getRoles()->delete();
                $crud->items()->delete();
                $crud->delete();
            });

            return redirect()->route('cruds.index')->with('status', $crud->title . ' modülü silindi.')->with('reload', true);
        }
        catch (\Exception $e)
        {
            report($e);

            return back()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
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
        catch (\Exception $e)
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

    public function relationshipStore(Request $request,Crud $crud): RedirectResponse
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
                return back()->withErrors($validator)->withInput();
            }

            $many = $request->get('relationship') === 'belongsToMany';

            $request->merge(
            [
                'relationship_pivot_table_name' => $many ? $request->get('relationship_pivot_table_name') : null,
                'relationship_column_name'      => $many ? null : $request->get('relationship_column_name'),
            ]);

            $detail =
                [
                    'type'           => $request->get('relationship'),
                    'table_name'     => $request->get('relationship_table_name'),
                    'model'          => $request->get('relationship_table_model'),
                    'column_name'    => $request->get('relationship_column_name') ?? null,
                    'pivot_table'    => $request->get('relationship_pivot_table_name') ?? null,
                    'show_column'    => $request->get('show_column'),
                    'match_column'   => $request->get('match_column'),
                    'multiple'       => $many,
                ];

            if ($many)
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
                $crudItem              = CrudItem::where('column_name',$request->get('relationship_column_name'))->where('crud_id',$crud->id)->first() ?? new CrudItem();
                $crudItem->column_name = $request->get('relationship_column_name');
                $crudItem->crud_id     = $crud->id;
            }

            $crudItem->title        = $request->get('relationship_title');
            $crudItem->detail       = json_encode($detail);
            $crudItem->form_type_id = 16;
            $crudItem->relationship = 1;
            $crudItem->save();

            return back()->with('status', $crudItem->title . ' ilişkisi kaydedildi.');
        }
        catch (\Exception $e)
        {
            report($e);

            return back()->withInput()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    public function relationshipDestroy(CrudItem $crudItem): RedirectResponse
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
                $crudItem->form_type_id = FormType::where('key', 'text')->value('id') ?? $crudItem->form_type_id;
                $crudItem->save();
            }

            return back()->with('status', $crudItem->title . ' ilişkisi kaldırıldı.');
        }
        catch (\Exception $e)
        {
            report($e);

            return back()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    public function repeaterDestroy(CrudItem $crudItem): RedirectResponse
    {
        try
        {
            $crudItem->repeater     = 0;
            $crudItem->detail       = '{}';
            $crudItem->form_type_id = FormType::where('key', 'text')->value('id') ?? $crudItem->form_type_id;
            $crudItem->save();

            return back()->with('status', $crudItem->title . ' tekrarlanan alanı kaldırıldı.');
        }
        catch (\Exception $e)
        {
            report($e);

            return back()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    public function repeaterStore(Request $request,Crud $crud): RedirectResponse
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
                return back()->withErrors($validator)->withInput();
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

            $crudItem               = $crud->items()->firstOrNew(['column_name' => $request->get('repeater_column_name')]);
            $crudItem->title        = $crudItem->title ?? $crudItem->column_name;
            $crudItem->order        = $crudItem->order ?? ($crud->items()->max('order') + 1);
            $crudItem->detail       = json_encode($repeaterData);
            $crudItem->form_type_id = 17;
            $crudItem->repeater     = 1;
            $crudItem->save();

            return back()->with('status', $crudItem->title . ' tekrarlanan alanı kaydedildi.');
        }
        catch (\Exception $e)
        {
            report($e);

            return back()->withInput()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }
}

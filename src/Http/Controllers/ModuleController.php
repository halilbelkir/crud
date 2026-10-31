<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Exceptions\ForeignKeyConstraintException;
use crudPackage\Models\Crud;
use Carbon\Carbon;
use crudPackage\Library\ImageUpload\ImageUpload;
use crudPackage\Library\Relationships\CrudRelationships;
use crudPackage\Models\DataTranslate;
use crudPackage\Models\FormType;
use crudPackage\Support\Actions;
use crudPackage\Support\Permissions;
use crudPackage\Support\Search;
use crudPackage\Support\Sortable;
use crudPackage\Support\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModuleController extends Controller
{
    public $slugPrefix;
    public $crud;
    public $relationshipNames;

    public $formTypes;
    public $languages;
    public $foreignKey = 0;


    public function __construct(Request $request)
    {
        $path = $request->path();

        if (strstr($path, '/'))
        {
            $path            = explode('/', $path);
            $this->slugPrefix = $path[0];
        }
        else
        {
            $this->slugPrefix = $path;
        }

        $this->crud              = Crud::where('slug', $this->slugPrefix)->first();
        $this->relationshipNames = CrudRelationships::getModelRelations($this->crud->model);
        $this->languages         = crudLanguages($this->crud);
    }

    public function index(Request $request, $locale = null)
    {
        $crud    = $this->crud;
        $area1   = isset($crud->area_1) ? json_decode($crud->area_1) : null;
        $model   = $crud->model;
        $columns = $this->browseColumns($locale);
        $search  = $crud->browseColumns->filter(fn ($column) => $column->relationship == 0 && $column->repeater == 0 && in_array($column->form_type_id, [9, 10, 11], true));

        $records = $model::query()
            ->when(count($this->relationshipNames) > 0, fn ($query) => $query->with($this->relationshipNames))
            ->when($request->filled('q') && $search->isNotEmpty(), function ($query) use ($request, $search)
            {
                $term = Search::pattern((string) $request->string('q'));

                $query->where(function ($inner) use ($search, $term)
                {
                    foreach ($search as $column)
                    {
                        $inner->orWhere($column->column_name, 'like', $term);
                    }
                });
            });

        if (isset($area1->order_column_name) && ! $request->filled('sirala'))
        {
            $records->orderByTranslate($area1->order_column_name, $area1->order_direction, $locale);
        }
        elseif (! $request->filled('sirala'))
        {
            $instance = new $model;
            $order    = Sortable::column();

            Schema::hasColumn($instance->getTable(), $order)
                ? $records->orderBy($order)->orderBy($instance->getQualifiedKeyName())
                : $records->orderByDesc($instance->getQualifiedKeyName());
        }

        $records = Table::sort($records, $columns);

        if ($export = Table::export($records, $columns, $crud->title))
        {
            return $export;
        }

        $rows      = $records->paginate(Table::perPage())->withQueryString();
        $total     = $model::count();
        $languages = $this->languages;

        return view('crudPackage::modules.index', compact('crud', 'rows', 'columns', 'total', 'locale', 'languages', 'search'));
    }

    protected function browseColumns($locale = null): array
    {
        $crud    = $this->crud;
        $columns = [];
        $first   = true;

        foreach ($crud->browseColumns as $column)
        {
            $details = json_decode($column->detail, true) ?? [];
            $value   = fn ($record) => $this->plainColumn($locale ? $record->getTranslate($locale) : $record, $column, $details);
            $related = $column->relationship == 1 && ($details['type'] ?? null) === 'belongsTo' && isset($details['model'], $details['show_column'], $details['match_column']);
            $sort    = $column->repeater == 0 && ($column->relationship == 0 || $related) ? $column->column_name : null;
            $order   = $related
                ? fn ($query, $direction) => $query->orderBy(
                    $details['model']::select($details['show_column'])
                        ->whereColumn((new $details['model'])->qualifyColumn($details['match_column']), $query->getModel()->qualifyColumn($column->column_name))
                        ->limit(1),
                    $direction
                )
                : null;

            if ($this->inlineEditable($column, $details))
            {
                $columns[] = [
                    'type'     => 'field',
                    'label'    => $column->title,
                    'width'    => $column->form_type_id == 15 ? '170px' : '1.2fr',
                    'sortable' => $sort,
                    'name'     => $column->column_name,
                    'value'    => $column->form_type_id == 15
                        ? fn ($record) => $record->{$column->column_name} == 1 ? ($details['on'] ?? 'Aktif') : ($details['off'] ?? 'Pasif')
                        : $value,
                    'url'      => fn ($record) => route($crud->slug . '.realtime', array_filter([$record->getKey(), $locale])),
                    'html'     => fn ($record) => $this->inlineField($record, $column, $locale),
                ];

                continue;
            }

            if ($column->form_type_id == 15)
            {
                $columns[] = [
                    'type'     => 'status',
                    'label'    => $column->title,
                    'width'    => '150px',
                    'sortable' => $sort,
                    'value'    => fn ($record) => $record->{$column->column_name} == 1 ? ($details['on'] ?? 'Aktif') : ($details['off'] ?? 'Pasif'),
                    'tone'     => fn ($record) => $record->{$column->column_name} == 1 ? 'success' : 'muted',
                ];

                continue;
            }

            if ($first)
            {
                $first     = false;
                $image     = $crud->browseColumns->first(fn ($item) => $item->form_type_id == 6 && $item->repeater == 0);
                $columns[] = [
                    'type'     => 'lead',
                    'label'    => $column->title,
                    'width'    => '1.6fr',
                    'sortable' => $sort,
                    'order'    => $order,
                    'name'     => $value,
                    'title'    => fn ($record) => $this->recordTitle($record, $locale),
                    'image'    => fn ($record) => $image ? $this->firstImage($record->{$image->column_name}) : null,
                ];

                continue;
            }

            if ($column->form_type_id == 6)
            {
                continue;
            }

            $columns[] = [
                'type'     => 'text',
                'label'    => $column->title,
                'sortable' => $sort,
                'order'    => $order,
                'value'    => $value,
            ];
        }

        $slug   = $crud->slug;
        $extra  = Permissions::allows($slug . '.create') && Route::has($slug . '.copy')
            ? fn ($record) => [['label' => 'Kopyala', 'icon' => 'copy', 'href' => route($slug . '.copy', $record->getKey()), 'drawer' => true, 'title' => $crud->display_single . ' kopyala']]
            : fn ($record) => [];

        $columns[] = [
            'type'  => 'actions',
            'width' => '60px',
            'menu'  => fn ($record) => Actions::menu($slug, $record,
            [
                'editTitle' => $crud->display_single . ' düzenle',
                'extra'     => $extra($record),
            ]),
        ];

        return $columns;
    }

    protected function inlineEditable($column, array $details): bool
    {
        return ! empty($details['realtime'])
            && $column->repeater == 0
            && empty($details['multiple'])
            && in_array($column->type?->key, ['text', 'number', 'date', 'datetime', 'select', 'select2', 'switch'], true)
            && Permissions::allows($this->crud->slug . '.edit');
    }

    protected function inlineField($record, $column, $locale = null): string
    {
        $crud        = $this->crud;
        $formType    = $column->type;
        $type        = $formType->key;
        $language    = $locale ? multipleLanguages(1, $locale) : null;
        $languageKey = $locale ? multipleLanguages(2, $locale) : null;
        $value       = $locale ? $record->getTranslate($locale) : $record;
        $size        = 'sm';

        return view('crudPackage::formTypes.' . $formType->group, compact('column', 'type', 'crud', 'formType', 'language', 'languageKey', 'value', 'size'))->render();
    }

    public function realtime(Request $request, string $id, $locale = null)
    {
        $crud    = $this->crud;
        $column  = $crud->items->first(fn ($item) => $item->column_name === (string) $request->input('column') && ($item->browse == 1 || $item->read == 1));
        $details = $column ? (json_decode($column->detail, true) ?? []) : [];

        abort_unless($column && $this->inlineEditable($column, $details), 403);

        $record      = $crud->model::findOrFail($id);
        $languageKey = $locale ? multipleLanguages(2, $locale) : 0;
        $rules       = [$column->required == 1 && empty($languageKey) ? 'required' : 'nullable'];

        if (isset($details['maxlength']))
        {
            $rules[] = 'max:' . $details['maxlength'];
        }

        if (isset($details['items']) && $column->relationship == 0)
        {
            $rules[] = 'in:' . implode(',', array_keys($details['items']));
        }

        $request->validate(
            [
                'value' => $rules,
            ],
            [],
            [
                'value' => $column->title,
            ]
        );

        $value = $request->input('value');
        $value = $column->form_type_id == 15 ? ((string) $value === '1' ? 1 : 0) : (filled($value) ? $value : null);

        if (! empty($languageKey))
        {
            $translate = DataTranslate::where('model', $crud->model)
                ->where('locale', $locale)
                ->where('foreign_key', $record->getKey())
                ->where('column_name', $column->column_name)
                ->first() ?? new DataTranslate();

            $translate->model       = $crud->model;
            $translate->locale      = $locale;
            $translate->foreign_key = $record->getKey();
            $translate->column_name = $column->column_name;
            $translate->value       = $value;
            $translate->save();
        }
        else
        {
            $record->{$column->column_name} = $value;
            $record->save();
        }

        return response()->json(
            [
                'status' => $column->title . ' güncellendi.',
            ]
        );
    }

    protected function firstImage($value): ?string
    {
        $decoded = is_string($value) ? json_decode($value, true) : null;
        $path    = is_array($decoded) ? ($decoded[0] ?? null) : $value;

        return filled($path) && is_string($path) ? uploadUrl($path) : null;
    }

    protected function titleColumn()
    {
        $crud    = $this->crud;
        $columns = $crud->items->where('repeater', 0)->where('relationship', 0)->whereIn('form_type_id', [10, 11]);

        foreach (config('crudPanel.title_columns', []) as $name)
        {
            if ($column = $columns->firstWhere('column_name', $name))
            {
                return $column;
            }
        }

        return $columns->first(fn ($column) => ! in_array($column->column_name, [$crud->order_column_name, 'order', 'slug'], true) && ! str_ends_with($column->column_name, '_id'));
    }

    protected function recordTitle($record, $locale = null): ?string
    {
        $column = $this->titleColumn();

        if (! $column)
        {
            return null;
        }

        $value = $this->plainColumn($locale ? $record->getTranslate($locale) : $record, $column, json_decode($column->detail, true) ?? []);

        return filled($value) ? Str::limit($value, 60) : null;
    }

    protected function plainColumn($value, $column, array $details): string
    {
        if ($column->repeater == 1)
        {
            $items = json_decode((string) $value->{$column->column_name}, true);

            return is_array($items) ? count($items) . ' satır' : '';
        }

        if ($column->form_type_id == 1)
        {
            $items = json_decode((string) $value->{$column->column_name}, true);

            return is_array($items) ? implode(', ', $items) : (string) ($value->{$column->column_name} ?? '');
        }

        if (in_array($column->form_type_id, [5, 6], true))
        {
            $items = json_decode((string) $value->{$column->column_name}, true);

            return is_array($items) ? count($items) . ' dosya' : basename((string) ($value->{$column->column_name} ?? ''));
        }

        if ($column->form_type_id == 9)
        {
            return Str::limit(trim(html_entity_decode(strip_tags((string) $value->{$column->column_name}))), 80);
        }

        $text = trim(strip_tags((string) $this->renderStandardColumn($value, $column, $details, $column->column_name)));

        if ($text === '' && $column->relationship == 0 && in_array($column->form_type_id, [10, 11, 13]))
        {
            return trim((string) ($value->{$column->column_name} ?? ''));
        }

        return $text;
    }

    public function columns($status = 0,$language = null, $languageKey = null,$value = null,$copy = null)
    {
        $crud              = $this->crud;
        $elements          = '';
        $languageTitle     = $language != null ? ' ('.$language->title.') ' : null;
        $languageForAttr   = $language != null ? '_'.$language->code : null;
        $columns           = empty($status) ? $crud->addColumns : $crud->editColumns;
        $originalValue     = $value;
        $value             = isset($value) ? ( isset($language) ? $value->getTranslate($language->code) : $value ) : null;

        foreach($columns as $column)
        {
            $formType  = $column->type;
            $type      = $formType->key;
            $info      = null;

            if ($column->repeater == 1)
            {
                $details      = json_decode($column->detail);
                $rElements    = '';
                $elementsView = '';
                $repeaterList = isset($language) ? $language->code . '[' . $column->column_name . ']' : $column->column_name;
                $template     = $this->repeaterTemplate($details, $repeaterList, $crud, $language, $languageKey);

                if (old() && is_array($oldRows = old(crudFieldKey($repeaterList))))
                {
                    foreach (array_values($oldRows) as $elementKey => $elementValue)
                    {
                        $rElements = '';

                        foreach ($details as $detail)
                        {
                            $detail->required      = isset($detail->validation) && str_contains($detail->validation, 'required') ? 1 : 0;
                            $detail->repeater      = 1;
                            $detail->repeater_name = $repeaterList . '[' . $elementKey . ']';

                            $formTypeR  = FormType::find($detail->form_type_id);
                            $rElements .= $this->repeaterField($detail, view('crudPackage::formTypes.' . $formTypeR->group, ['column' => $detail, 'formType' => $formTypeR, 'type' => $formTypeR->key, 'value' => (object) $elementValue], compact('crud', 'language', 'languageKey'))->render());
                        }

                        $elementsView .= crudComponent('repeater-item', ['no' => $elementKey], $rElements);
                    }

                    $elements .= view('crudPackage::formTypes.' . $formType->group, ['elements' => $elementsView, 'rows' => count($oldRows)], compact('column', 'type', 'crud', 'formType', 'language', 'languageKey', 'template'))->render();

                    continue;
                }

                if (isset($value))
                {
                    $elementValues = array_values((array) (json_decode((string) $value->{$column->column_name}) ?? []));

                    foreach($elementValues as $elementKey => $elementValue)
                    {
                        $rElements    = '';

                        foreach ($details as $detail)
                        {
                            $detail->required      = isset($detail->validation) && str_contains($detail->validation, 'required') ? 1 : 0;
                            $detail->repeater      = 1;
                            $detail->repeater_name = $repeaterList . '[' . $elementKey . ']';

                            $formTypeR  = FormType::find($detail->form_type_id);
                            $typeR      = $formTypeR->key;
                            $rElements .= $this->repeaterField($detail, view('crudPackage::formTypes.'. $formTypeR->group,['column' => $detail,'formType' => $formTypeR,'type' => $typeR,'value' => $elementValue],compact('crud','language','languageKey'))->render());
                        }

                        $elementsView .= crudComponent('repeater-item', ['no' => $elementKey], $rElements);
                    }

                    $elements .= view('crudPackage::formTypes.'. $formType->group,['elements' => $elementsView, 'rows' => count($elementValues)],compact('column','type','crud','formType','language','languageKey','value','originalValue','template'))->render();
                }
                else
                {
                    $elements .= view('crudPackage::formTypes.'. $formType->group,['elements' => str_replace('__index__', '0', $template), 'rows' => 1],compact('column','type','crud','formType','language','languageKey','template'))->render();
                }
            }
            else
            {
                $details = json_decode($column->detail);

                if (count((array)$details) > 0)
                {
                    $info = $details->info ?? null;

                    if (isset($details->type) && $details->type == 'belongsToMany' && !empty($languageKey))
                    {
                        continue;
                    }
                }

                $elements .= crudComponent('field',
                    [
                        'label'      => $column->title . $languageTitle,
                        'for'        => $column->column_name . $languageForAttr,
                        'required'   => $column->required == 1,
                        'info'       => $info,
                        'hidden'     => $column->form_type_id == 13,
                        'wide'       => in_array($column->form_type_id, [1, 9, 10], true) || ! empty($details->multiple),
                        'labelClass' => 'w-100',
                    ],
                    view('crudPackage::formTypes.'. $formType->group,compact('column','type','crud','formType','language','languageKey','value','copy','originalValue'))->render()
                );
            }
        }

        return $elements;
    }

    private function filledRows($rows): array
    {
        return array_values(array_filter((array) $rows, fn ($row) => is_array($row) && count(array_filter($row, 'filled')) > 0));
    }

    private function repeaterTemplate($details, string $repeaterList, $crud, $language, $languageKey): string
    {
        $content = '';

        foreach ($details as $detail)
        {
            $detail->required      = isset($detail->validation) && str_contains($detail->validation, 'required') ? 1 : 0;
            $detail->repeater      = 1;
            $detail->repeater_name = $repeaterList . '[__index__]';

            $formType = FormType::find($detail->form_type_id);
            $content .= $this->repeaterField($detail, view('crudPackage::formTypes.' . $formType->group, ['column' => $detail, 'formType' => $formType, 'type' => $formType->key], compact('crud', 'language', 'languageKey'))->render());
        }

        return crudComponent('repeater-item', ['no' => '__index__'], $content);
    }

    private function repeaterField($detail, string $content): string
    {
        preg_match('/col(?:-[a-z]+)?-(\d{1,2})\b/', (string) ($detail->class ?? ''), $match);

        return crudComponent('field',
            [
                'label'      => $detail->title,
                'for'        => 'repeater_' . $detail->column_name,
                'required'   => $detail->required == 1,
                'hidden'     => $detail->form_type_id == 13,
                'span'       => min(12, max(1, (int) ($match[1] ?? 6))),
                'labelClass' => 'w-100',
            ],
            $content
        );
    }

    public function create()
    {
        $crud        = $this->crud;
        $elements    = '';
        $elementTabs = [];

        if (count($this->languages) > 0)
        {
            foreach($this->languages as $languageKey => $language)
            {
                $elementTabs[$language->code] = $this->columns(0,$language,$languageKey);
            }
        }
        else
        {
            $elements = $this->columns(0);
        }

        return view(request()->ajax() ? 'crudPackage::modules.form' : 'crudPackage::modules.create',compact('crud','elements','elementTabs'));
    }

    public function dynamicValidation($request,$status = 0,$data = null)
    {
        $crud      = $this->crud;
        $attribute = [];
        $rules     = [];
        $columns   = $crud->addColumns;

        if ($status == 1)
        {
            $columns = $crud->editColumns;
        }

        foreach ($columns as $column)
        {
            $details                = json_decode($column->detail);
            $columnName             = $column->column_name;
            $attribute[$columnName] = $column->title;

            if ($column->required == 1 && isset($details->validation))
            {
                $rules[$columnName] = array_merge(['required'], explode('|',$details->validation));
            }
            else if ($column->required == 0 && isset($details->validation))
            {
                $rules[$columnName] = explode('|',$details->validation);
            }
            else if ($column->required == 1 && !isset($details->validation))
            {
                $rules[$columnName] = ['required'];
            }

            if (isset($details->validation) && strstr($details->validation, 'unique'))
            {
                $rules[$columnName] = array_map(function ($rule) use ($columnName, $status, $data)
                {
                    $parts = str_starts_with($rule, 'unique:') ? explode(',', substr($rule, 7)) : [];

                    if (count($parts) === 0 || count($parts) > 2)
                    {
                        return $rule;
                    }

                    return 'unique:' . $parts[0] . ',' . ($parts[1] ?? $columnName) . ($status == 1 ? ',' . $data->id : '');
                }, $rules[$columnName]);
            }

            if ($column->form_type_id == 1)
            {
                if (strstr($rules[$columnName],'|'))
                {
                    $validations = explode('|',$rules[$columnName]);
                }
                else
                {
                    $validations = [$rules[$columnName]];
                }

                $validations[] = function ($attribute, $value, $fail) use ($column)
                {
                    $decoded = json_decode($value, true);

                    if (!is_array($decoded) || empty($decoded))
                    {
                        $fail($column->title.' alanı en az bir değer içermelidir.');
                    }
                };

                $rules[$columnName] = $validations;
            }

            if ($column->repeater == 1 && $column->required == 1)
            {
                array_push($rules[$columnName],'array');

                $rules[$columnName][] = function ($attribute, $value, $fail) use ($column)
                {
                    $language = strstr($attribute, '.', true);

                    if ($language !== false && $language !== ($this->languages[0]->code ?? null))
                    {
                        return;
                    }

                    if (count($this->filledRows($value)) === 0)
                    {
                        $fail($column->title . ' alanı gereklidir.');
                    }
                };

                foreach ($details as $detail)
                {
                    if (isset($detail->validation))
                    {
                        $rules[$columnName.'.*.'.$detail->column_name]     = explode('|',$detail->validation);
                        $attribute[$columnName.'.*.'.$detail->column_name] = $detail->title;
                    }
                }
            }

            if ($column->required == 16 && isset($details->multiple))
            {
                array_push($rules[$columnName],'array');
                array_push($rules[$columnName],'min:1');

                $rules[$columnName.'.*']     = [explode('|',$rules[$columnName])];
                $attribute[$columnName.'.*'] = $attribute[$columnName];
            }

            if (in_array($column->form_type_id, [5, 6, 14]) && isset($data) && !empty($data->$columnName))
            {
                unset($rules[$columnName]);
            }
        }

        if (count($this->languages) > 0)
        {
            $newAttribute  = [];
            $newRules      = [];

            foreach($this->languages as $languageKey => $language)
            {
                foreach ($attribute as $attributeKey => $attributeValue)
                {
                    $activeRule = $rules[$attributeKey] ?? null;

                    if (!empty($activeRule))
                    {
                        $newAttribute["{$language->code}.{$attributeKey}"] = $attributeValue.' ('.$language->title .')';
                        $newRules["{$language->code}.{$attributeKey}"]     = $activeRule;

                        if ($languageKey > 0 && in_array('required',array_values($activeRule)))
                        {
                            array_push($newRules["{$language->code}.{$attributeKey}"],'nullable');

                            $newRules["{$language->code}.{$attributeKey}"] = array_values(
                                array_filter($newRules["{$language->code}.{$attributeKey}"], fn ($rule) => $rule !== 'required')
                            );
                        }
                    }
                }
            }

            $attribute = $newAttribute;
            $rules     = $newRules;
        }

        return [$attribute,$rules];
    }

    public function insertAndUpdate($request,$allData,$allFiles = null,$language = null, $orderLanguage = null,$data = null)
    {
        $crud          = $this->crud;
        $images        = [];
        $oldImages     = [];
        $belongsToMany = [];
        $columns       = isset($data) ? $crud->editColumns : $crud->addColumns;

        foreach ($columns as $columnKey => $column)
        {
            $formType  = $column->form_type_id;
            $details   = json_decode($column->detail,true);
            $removeKey = $column->column_name . '_remove';
            $remove    = in_array($formType, [5, 6]) && empty($details['multiple']) && ! empty($allData[$removeKey]) && empty($allFiles[$column->column_name]);
            $copyKey   = $column->column_name . '_copy';
            $copyKeep  = isset($allData[$copyKey]) ? array_values(array_filter((array) $allData[$copyKey], 'filled')) : null;

            unset($allData[$removeKey], $allData[$copyKey]);

            if ($formType == 14 && isset($data) && blank($allData[$column->column_name] ?? null))
            {
                unset($allData[$column->column_name]);

                continue;
            }

            if ($remove && isset($data) && ! (isset($language) && $orderLanguage > 0) && filled($data->{$column->column_name}))
            {
                (new ImageUpload())->delete($data->{$column->column_name});

                $allData[$column->column_name] = null;
            }

            if ($formType == 15)
            {
                $columnValue = $allData[$column->column_name];

                if ($details['on'] ==  $columnValue)
                {
                    $allData[$column->column_name] = 1;
                }
                else if ($details['off'] ==  $columnValue)
                {
                    $allData[$column->column_name] = 0;
                }
            }
            if ($column->repeater == 1)
            {
                $allData[$column->column_name] = json_encode($this->filledRows($allData[$column->column_name] ?? []), JSON_UNESCAPED_UNICODE);
            }

            if ($column->relationship == 1 && ($details['type'] ?? null) == 'belongsToMany' && array_key_exists($column->column_name, $allData) && blank($allData[$column->column_name]))
            {
                unset($allData[$column->column_name]);

                if (isset($data) && ! (isset($language) && $orderLanguage > 0))
                {
                    $belongsToMany[] =
                        [
                            'detail' => $column->detail,
                            'values' => [],
                        ];
                }

                continue;
            }

            if ($column->relationship == 1 && isset($allData[$column->column_name]))
            {
                $detail = json_decode($column->detail);

                if ($detail->type == 'belongsToMany')
                {
                    $belongsToMany[] =
                        [
                            'detail' => $column->detail,
                            'values' => $allData[$column->column_name]
                        ];
                    $allData[$column->column_name]['belongsToMany'] = 1;
                }
            }

            if (isset($allData['crud_copy_id']))
            {
                $model     = $crud->model;
                $copyData  = $model::find($allData['crud_copy_id']);
                $copyValue = $copyData->{$column->column_name};

                if (in_array($formType, [5, 6]) && isset($language) && $orderLanguage > 0)
                {
                    $copyValue = $copyData->getTranslate($language->code)->translated($column->column_name);
                }

                if ($remove)
                {
                    $allData[$column->column_name] = null;
                }
                else if (isset($copyValue))
                {
                    if ($formType == 5 || $formType == 6)
                    {
                        $imageUpload = new ImageUpload();

                        if (isset($details['multiple']) && $details['multiple'] == true)
                        {
                            $files = [];

                            foreach(json_decode($copyValue) as $order => $oldFile)
                            {
                                if ($copyKeep !== null && ! in_array($oldFile, $copyKeep, true))
                                {
                                    continue;
                                }

                                $extension    = pathinfo($oldFile, PATHINFO_EXTENSION);
                                $newFileName  = $crud->table_name.'/'.$crud->table_name.'-'.$order.'-'.random_int(100000000, 999999999999).time().'.'.$extension;
                                $fileName      = $imageUpload->copy($oldFile, $newFileName);
                                $files[]       = $fileName;
                            }

                            $allData[$column->column_name] = json_encode($files,JSON_UNESCAPED_UNICODE,true);
                        }
                        else
                        {
                            $oldFile    = $copyValue;
                            $extension  = pathinfo($oldFile, PATHINFO_EXTENSION);
                            $newPath    = $crud->table_name.'/'.$crud->table_name.'-'.random_int(100000000, 999999999999).time().'.'.$extension;
                            $fileName    = $imageUpload->copy($oldFile, $newPath);

                            $allData[$column->column_name] = $fileName;
                        }
                    }
                }
            }
        }

        if (is_array($allFiles) && count($allFiles) > 0)
        {
            $imageUpload = new ImageUpload();

            foreach ($allFiles as $inputName => $file)
            {
                if (is_array($file))
                {
                    $file = array_filter($file, fn($f) => $f instanceof \Illuminate\Http\UploadedFile && $f->isValid());

                    if (empty($file))
                    {
                        continue;
                    }

                    foreach($file as $order => $mFile)
                    {
                        $name      = $crud->table_name.'-'.$order.'-'.random_int(100000000, 999999999999).time().(isset($language) ? '-'.$language->code : null);
                        $fileName   = $imageUpload->getName($mFile,$name,$crud->table_name);
                        $images[]  = $fileName;
                    }

                    if (isset($allData['crud_copy_id']))
                    {
                        $oldFiles            = json_decode($allData[$inputName]);
                        $merge               = array_merge($oldFiles,$images);
                        $allData[$inputName] = json_encode($merge,JSON_UNESCAPED_UNICODE,true);
                    }
                    else
                    {
                        if (isset($language) && $orderLanguage > 0 && isset($data))
                        {
                            $existingTranslate = DataTranslate::where('foreign_key', $data->id)
                                ->where('locale', $language->code)
                                ->where('model', $crud->model)
                                ->where('column_name', $inputName)
                                ->first();

                            if ($existingTranslate && !empty($existingTranslate->value))
                            {
                                $inputValues         = json_decode($existingTranslate->value, true) ?? [];
                                $merge               = array_merge($images, $inputValues);
                                $allData[$inputName] = json_encode($merge, JSON_UNESCAPED_UNICODE, true);
                            }
                            else
                            {
                                $allData[$inputName] = json_encode($images, JSON_UNESCAPED_UNICODE, true);
                            }
                        }
                        else if (isset($data->$inputName))
                        {
                            if (!is_array(json_decode($data->$inputName,true)))
                            {
                                $inputValues = [$data->$inputName];
                            }
                            else
                            {
                                $inputValues = json_decode($data->$inputName,true);
                            }

                            $merge               = array_merge($images,$inputValues);
                            $allData[$inputName] = json_encode($merge,JSON_UNESCAPED_UNICODE,true);
                        }
                        else
                        {
                            $allData[$inputName] = json_encode($images,JSON_UNESCAPED_UNICODE,true);
                        }
                    }
                }
                else
                {
                    if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid())
                    {
                        continue;
                    }

                    if (isset($data) && isset($language) && $orderLanguage > 0)
                    {
                        $newData = DataTranslate::where('foreign_key',$data->id)->where('locale',$language->code)->where('model',$crud->model)->where('column_name',$inputName)->first();

                        if (isset($newData))
                        {
                            $imageUpload->delete($newData->value);
                        }
                    }
                    else if (isset($data))
                    {
                        $imageUpload->delete($data->$inputName);
                    }

                    $name                = $crud->table_name.'-'.random_int(100000000, 999999999999).time().(isset($language) ? '-'.$language->code : null);
                    $fileName             = $imageUpload->getName($file,$name,$crud->table_name);
                    $allData[$inputName] = $fileName;
                }
            }
        }

        unset($allData['crud_copy_id']);
        unset($allData['_method']);

        if (isset($language) && $orderLanguage > 0)
        {
            foreach ($allData as $key => $otherValue)
            {
                $dataTranslate        = DataTranslate::where('foreign_key',($data->id ?? 0))->where('locale',$language->code)->where('model',$crud->model)->where('column_name',$key)->first();
                $newData              = isset($data) && !empty($dataTranslate) ? $dataTranslate : new DataTranslate();
                $newData->model       = $crud->model;
                $newData->column_name = $key;
                $newData->foreign_key = $this->foreignKey;
                $newData->locale      = $language->code;
                $newData->value       = is_array($otherValue) ? json_encode($otherValue) : $otherValue;
                $newData->save();

                if (count($belongsToMany) > 0)
                {
                    foreach ($belongsToMany as $key => $belongsTo)
                    {
                        $detail = json_decode($belongsTo['detail']);

                        if (isset($data))
                        {
                            DB::table($detail->pivot_table)->where($detail->foreign_key,$newData->id)->delete();
                        }

                        foreach ($belongsTo['values'] as $k => $v)
                        {
                            DB::table($detail->pivot_table)->insert(
                                [
                                    $detail->foreign_key => $newData->id,
                                    $detail->related_key => $v
                                ]);
                        }
                    }
                }
            }
        }
        else
        {
            $newData = isset($data) ? $data : new $crud->model();

            foreach ($allData as $key => $value)
            {
                if (!isset($value['belongsToMany']))
                {
                    $newData->$key = is_array($value) ? json_encode($value) : $value;
                }
            }

            $newData->save();

            $this->foreignKey = $newData->id;

            if (count($belongsToMany) > 0)
            {
                foreach ($belongsToMany as $key => $belongsTo)
                {
                    $detail = json_decode($belongsTo['detail']);

                    if (isset($data))
                    {
                        DB::table($detail->pivot_table)->where($detail->foreign_key,$this->foreignKey)->delete();
                    }

                    foreach ($belongsTo['values'] as $k => $v)
                    {
                        DB::table($detail->pivot_table)->insert(
                            [
                                $detail->foreign_key => $this->foreignKey,
                                $detail->related_key => $v
                            ]);
                    }
                }
            }
        }
    }

    public function store(Request $request)
    {
        try
        {
            $copyData = null;
            $status   = 0;
            $crud     = $this->crud;
            $allData  = $request->except('_token');
            $allFiles = $request->allFiles();

            if (isset($allData['crud_copy_id']))
            {
                $model    = $crud->model;
                $copyData = $model::find($allData['crud_copy_id']);
            }

            $validators = $this->dynamicValidation($request,$status,$copyData);
            $validator  = Validator::make($request->all(), $validators[1]);
            $validator->setAttributeNames($validators[0]);

            $validator->validate();

            if (count($this->languages) > 0)
            {
                $allDataNew  = [];
                $allFilesNew = [];

                foreach ($this->languages as $languageKey => $language)
                {
                    $allDataNew  = $allData[$language->code];
                    $allFilesNew = $allFiles[$language->code] ?? null;

                    if (isset($allData['crud_copy_id']))
                    {
                        $allDataNew['crud_copy_id'] = $allData['crud_copy_id'];
                    }

                    $this->insertAndUpdate($request,$allDataNew,$allFilesNew,$language,$languageKey);
                }
            }
            else
            {
                $this->insertAndUpdate($request,$allData,$allFiles);
            }

            self::cacheClear();

            $otherRoute = $crud->other_route;
            $otherRoute = json_decode($otherRoute);

            if (isset($otherRoute->route))
            {
                $params  = [];
                $allData = (count($this->languages) > 0) ? $allData[$this->languages[0]->code] : $allData;

                if (isset($otherRoute->params))
                {
                    foreach ($otherRoute->params as $param)
                    {
                        $params[$param] = $allData[$param];
                    }
                }

                $otherRoute = route($otherRoute->route,$params);
            }
            else
            {
                $otherRoute = route($crud->slug .'.index');
            }

            return redirect($request->ajax() ? url()->previous() : $otherRoute)->with('status', ($crud->display_single ?: 'Kayıt') . ' eklendi.');
        }
        catch (ValidationException $e)
        {
            throw $e;
        }
        catch (\Exception $e)
        {
            \Illuminate\Support\Facades\Log::error('ModuleController@store: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    public function update(Request $request, string $id)
    {
        try
        {
            $crud       = $this->crud;
            $model      = $crud->model;
            $data       = $model::find($id);
            $allFiles   = $request->allFiles();
            $allData    = $request->except('_token');

            $validators = $this->dynamicValidation($request,1,$data);
            $validator  = Validator::make($request->all(), $validators[1]);

            $validator->setAttributeNames($validators[0]);

            $validator->validate();

            if (count($this->languages) > 0)
            {
                $allDataNew  = [];
                $allFilesNew = [];

                foreach ($this->languages as $languageKey => $language)
                {
                    $allDataNew  = $allData[$language->code];
                    $allFilesNew = $allFiles[$language->code] ?? null;

                    $this->insertAndUpdate($request,$allDataNew,$allFilesNew,$language,$languageKey,$data);
                }
            }
            else
            {
                $this->insertAndUpdate($request,$allData,$allFiles,null,null,$data);
            }

            $route      = $crud->only_edit == 1 ? route($crud->slug.'.edit',$data->id) : route($crud->slug .'.index');
            $otherRoute = $crud->other_route;
            $otherRoute = json_decode($otherRoute);

            if (isset($otherRoute->route))
            {
                $params  = [];
                $allData = (count($this->languages) > 0) ? $allData[$this->languages[0]->code] : $allData;

                if (isset($otherRoute->params))
                {
                    foreach ($otherRoute->params as $param)
                    {
                        $params[$param] = $allData[$param];
                    }
                }

                $otherRoute = route($otherRoute->route,$params);
            }
            else
            {
                $otherRoute = $route;
            }

            self::cacheClear();

            return redirect($request->ajax() ? url()->previous() : $otherRoute)->with('status', ($crud->display_single ?: 'Kayıt') . ' güncellendi.');
        }
        catch (ValidationException $e)
        {
            throw $e;
        }
        catch (\Exception $e)
        {
            \Illuminate\Support\Facades\Log::error('ModuleController@update: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return back()->withInput()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    public function show(string $id)
    {
        $crud   = $this->crud;
        $model  = $crud->model;
        $record = $model::query()
            ->when(count($this->relationshipNames) > 0, fn ($query) => $query->with($this->relationshipNames))
            ->findOrFail($id);

        $sections = [];
        $locales  = count($this->languages) > 0 ? collect($this->languages)->pluck('title', 'code')->all() : [null => null];

        foreach ($locales as $code => $title)
        {
            $translated = $code ? $record->getTranslate($code) : $record;
            $rows       = [];

            foreach ($crud->readColumns as $column)
            {
                $details = json_decode($column->detail, true) ?? [];
                $raw     = $translated->{$column->column_name} ?? null;
                $images  = [];

                if ($column->form_type_id == 6 && filled($raw))
                {
                    $decoded = json_decode((string) $raw, true);
                    $images  = array_map('uploadUrl', is_array($decoded) ? $decoded : [$raw]);
                }

                $rows[] = [
                    'label'  => $column->title,
                    'value'  => $column->form_type_id == 15
                        ? ($raw == 1 ? ($details['on'] ?? 'Aktif') : ($details['off'] ?? 'Pasif'))
                        : $this->plainColumn($translated, $column, $details),
                    'html'   => $column->form_type_id == 9 ? (string) $raw : null,
                    'images' => $images,
                    'inline' => $this->inlineEditable($column, $details)
                        ? [
                            'url'  => route($crud->slug . '.realtime', array_filter([$record->getKey(), $code])),
                            'name' => $column->column_name,
                            'html' => $this->inlineField($record, $column, $code ?: null),
                        ]
                        : null,
                ];
            }

            $sections[(string) $code] = ['title' => $title, 'rows' => $rows];
        }

        $title = $this->recordTitle($record);

        return view('crudPackage::modules.detail', compact('crud', 'record', 'sections', 'title'));
    }

    public function getEdit(string $id,$copy = 0)
    {
        $crud              = $this->crud;
        $model             = $crud->model;
        $value             = $model::find($id);
        $elementTabs       = [];
        $elements          = '';
        $copyStatus        = $copy == 1 ? 0 : 1;
        $breadcrumbs       =
            [
                'activePage'      => $crud->display_single.($copy == 1 ? ' Kopyala' : ' Düzenle'),
                'parentPage'      => $crud->display_plural,
                'parentPageRoute' => route($crud->slug .'.index')
            ];

        if($crud->only_edit == 1)
        {
            $breadcrumbs =
                [
                    'activePage' => $crud->display_single
                ];
        }

        if (count($this->languages) > 0)
        {
            foreach($this->languages as $languageKey => $language)
            {
                $elementTabs[$language->code] = $this->columns($copyStatus,$language,$languageKey,$value,$copy);
            }
        }
        else
        {
            $elements = $this->columns($copyStatus,null,null,$value,$copy);
        }

        return [
            'crud'        => $crud,
            'elements'    => $elements,
            'elementTabs' => $elementTabs,
            'value'       => $value,
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    public function copy(string $id)
    {
        return request()->ajax()
            ? view('crudPackage::modules.form', [...$this->getEdit($id,1), 'copying' => true])
            : view('crudPackage::modules.copy',$this->getEdit($id,1));
    }

    public function edit(string $id)
    {
        return view(request()->ajax() ? 'crudPackage::modules.form' : 'crudPackage::modules.edit',$this->getEdit($id));
    }

    public function destroy(string $id)
    {
        try
        {
            $crud          = $this->crud;
            $model         = $crud->model;
            $value         = $model::find($id);
            $imageUpload   = new ImageUpload();
            $formTypeIds   = [5,6];
            $deletedImages = [];

            foreach ($crud->items as $columnKey => $column)
            {
                if (in_array($column->form_type_id,$formTypeIds))
                {
                    $details  = json_decode($column->detail,true);

                    if (isset($details['multiple']) && $details['multiple'] == true)
                    {
                        $deletedImages = !empty($value->{$column->column_name}) ? array_merge($deletedImages,json_decode($value->{$column->column_name})) : $deletedImages;

                        $languageValuesMultiple = DataTranslate::where('model',$crud->model)->where('foreign_key',$id)->where('column_name',$column->column_name)->get();

                        foreach ($languageValuesMultiple as $languageValueMultiple)
                        {
                            $deletedImages = !empty($languageValueMultiple->value) ? array_merge($deletedImages,json_decode($languageValueMultiple->value)) : $deletedImages;
                        }
                    }
                    else
                    {
                        array_push($deletedImages,$value->{$column->column_name});

                        $languageValues = DataTranslate::where('model',$crud->model)->where('foreign_key',$id)->where('column_name',$column->column_name)->get();

                        foreach ($languageValues as $languageValue)
                        {
                            array_push($deletedImages,$languageValue->value);
                        }
                    }
                }

                if ($column->relationship == 1)
                {
                    $detail = json_decode($column->detail);

                    if ($detail->type == 'belongsToMany')
                    {
                        DB::table($detail->pivot_table)->where($detail->foreign_key,$id)->delete();
                    }
                }
            }

            $value->safeDelete();
            $value->deleteTranslation();
            $imageUpload->delete($deletedImages);

            self::cacheClear();

            $otherRoute = $crud->other_route;
            $otherRoute = json_decode($otherRoute);

            if (isset($otherRoute->route))
            {
                $params = [];

                if (isset($otherRoute->params))
                {
                    foreach ($otherRoute->params as $param)
                    {
                        $params[$param] = $value->{$param} ?? null;
                    }
                }

                $otherRoute = route($otherRoute->route,$params);
            }
            else
            {
                $otherRoute = route($crud->slug .'.index');
            }

            return redirect($otherRoute)->with('status', ($crud->display_single ?: 'Kayıt') . ' silindi.');
        }
        catch (\Exception $e)
        {
            if ($e instanceof ForeignKeyConstraintException && ($e->errorInfo()[1] ?? null) == 1451)
            {
                return back()->with('error', $e->getMessage() . ' İlişkili tablolar: ' . implode(', ', $e->relations()));
            }

            return back()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }

    private function renderRelationshipColumn($value, $column, $details, $columnName)
    {
        $relationship = CrudRelationships::generateName($this->crud->model, $column->column_name);
        $showColumn   = $details['show_column'] ?? 'name';

        if (!empty($details['multiple']) && $details['multiple'] === true)
        {
            $showValues = [];

            if ($details['type'] == 'belongsToMany')
            {
                $relationData = $value->{$relationship};

                if ($relationData && $relationData->isNotEmpty())
                {
                    foreach ($relationData as $item)
                    {
                        $showValues[] = $item->{$showColumn};
                    }
                }
            }
            else
            {

                $values = json_decode($value->{$columnName}, true) ?? [];

                if (!empty($values))
                {
                    $relatedModel = $details['model'];
                    $matchColumn  = $details['match_column'];

                    $relatedItems = $relatedModel::whereIn($matchColumn, $values)->get()->keyBy($matchColumn);

                    foreach ($values as $itemId)
                    {
                        if (isset($relatedItems[$itemId]))
                        {
                            $showValues[] = $relatedItems[$itemId]->{$showColumn};
                        }
                    }
                }
            }

            return implode(', ', $showValues);
        }

        $relationModel = $value->{$relationship};

        if (!$relationModel)
        {
            return '';
        }

        return $relationModel->{$showColumn} ?? '';
    }

    private function tagBadges(array $values): string
    {
        $badges = '';

        foreach ($values as $item)
        {
            $badges .= crudComponent('badge', ['class' => 'me-1'], $item);
        }

        return $badges;
    }

    private function renderStandardColumn($value, $column, $details, $columnName)
    {
        $formType    = $column->form_type_id;
        $columnValue = $value->$columnName;

        if ($column->relationship == 1)
        {
            return $this->renderRelationshipColumn($value, $column, $details, $columnName);
        }

        if ($formType == 15)
        {
            return crudStatusBadge($columnValue, $details['on'] ?? null, $details['off'] ?? null, 'lg');
        }

        if ($formType == 12 || $formType == 16)
        {
            if (isset($details['items']) && is_array($details['items']))
            {
                $selectedValues = is_array($columnValue) ? $columnValue : (is_string($columnValue) ? json_decode($columnValue, true) : null);

                if (is_array($selectedValues))
                {
                    $selectedItems = [];

                    foreach ($selectedValues as $selectedValue)
                    {
                        if (isset($details['items'][$selectedValue]))
                        {
                            $selectedItems[] = $details['items'][$selectedValue];
                        }
                    }

                    return implode(', ', $selectedItems);
                }

                foreach ($details['items'] as $keyItem => $item)
                {
                    if ($keyItem == $columnValue)
                    {
                        return $item;
                    }
                }
            }

            return $columnValue ?? '';
        }


        if ($formType == 1)
        {
            $values = json_decode($columnValue);

            if (is_array($values))
            {
                return $this->tagBadges($values);
            }

            return $columnValue ?? '';
        }


        if ($column->relationship == 0 && !empty($details))
        {
            if (isset($details['format']))
            {
                try
                {
                    return Carbon::parse($columnValue)->format($details['format']);
                }
                catch (\Exception $e)
                {
                    return $columnValue ?? '';
                }
            }
        }

        return $columnValue ?? '';
    }

    public function fileDestroy($id,$order,$columnName,$languageCode = null,$languageOrder = null)
    {
        try
        {
            $crud        = $this->crud;
            $imageUpload = new ImageUpload();
            $files        = [];

            if (isset($languageOrder) && $languageOrder > 0)
            {
                $data   = DataTranslate::where('foreign_key',$id)->where('model',$crud->model)->where('locale',$languageCode)->where('column_name',$columnName)->first();
                $column = $data->value;
            }
            else
            {
                $model  = $crud->model;
                $data   = $model::find($id);
                $column = $data->$columnName;
            }

            foreach (json_decode($column) as $key =>  $fil)
            {
                if ($key != $order)
                {
                    $files[] = $fil;
                }
                else
                {
                    $imageUpload->delete($fil);
                }
            }

            if (isset($languageOrder) && $languageOrder > 0)
            {
                $data->value = json_encode($files);
            }
            else
            {
                $data->$columnName = json_encode($files);
            }

            $data->save();

            return back()->with('status', 'Dosya silindi.');
        }
        catch (\Exception $e)
        {
            return back()->with('error', 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.');
        }
    }
}
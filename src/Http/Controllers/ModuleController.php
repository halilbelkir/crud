<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use Carbon\Carbon;
use crudPackage\Library\ImageUpload\ImageUpload;
use crudPackage\Library\Relationships\CrudRelationships;
use crudPackage\Models\DataTranslate;
use crudPackage\Models\FormType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Session;
use Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

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
        $this->languages         = settings('languages');
    }

    /**
     * Display a listing of the resource.
     */
    public function index($locale = null)
    {
        $crud  = $this->crud;
        $area1 = isset($crud->area_1) ? json_decode($crud->area_1) : null;

        return view('crudPackage::modules.index',compact('crud','area1','locale'));
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
            $info      = '';

            if ($column->repeater == 1)
            {
                $details      = json_decode($column->detail);
                $rElements    = '';
                $elementsView = '';

                if (isset($value))
                {
                    $elementValues = json_decode($value->{$column->column_name});
                    $repeaterValue = [];

                    foreach($elementValues as $elementKey => $elementValue)
                    {
                        $rElements    = '';

                        foreach ($details as $detail)
                        {
                            if (isset($detail->validation) && strstr('required',$detail->validation))
                            {
                                $detail->required = 1;
                            }
                            else
                            {
                                $detail->required = 0;
                            }

                            $detail->repeater = 1;

                            $formTypeR  = FormType::find($detail->form_type_id);
                            $typeR      = $formTypeR->key;
                            $rElements .= '<div class="form-group '.($detail->form_type_id == 13 ? 'd-none' : null) .' '.(isset($detail->class) ? $detail->class : 'col').' mb-7 fv-plugins-icon-container">';
                            $rElements .= '<label class=" '.($detail->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="repeater_'.$detail->column_name.'">'.$detail->title.'</label>';
                            $rElements .= view('crudPackage::formTypes.'. $formTypeR->group,['column' => $detail,'formType' => $formTypeR,'type' => $typeR,'value' => $elementValue],compact('crud','language','languageKey'))->render();
                            $rElements .= '</div>';
                        }

                        $elementsView .= '<div data-item-no="'.$elementKey.'" data-repeater-item>';
                        $elementsView .= '<div class="form-group row">';
                        $elementsView .= '<div class="col-md-1 form-group handle"><i class="bi text-dark me-3 fs-4 bi-arrows-move"></i></div>';
                        $elementsView .= $rElements;
                        $elementsView .= '<div class="col-md-1 form-group"> <a href="javascript:;" data-repeater-delete class="btn btn-flex btn-tertiary mt-6"> <i class="ki-outline ki-trash  fs-3"></i> Sil </a> </div>';
                        $elementsView .= '</div>';
                        $elementsView .= '</div>';
                    }

                    $elements .= view('crudPackage::formTypes.'. $formType->group,['elements' => $elementsView],compact('column','type','crud','formType','language','languageKey','value','originalValue'))->render();
                }
                else
                {
                    foreach($details as $detail)
                    {
                        if (isset($detail->validation) && strstr('required',$detail->validation))
                        {
                            $detail->required = 1;
                        }
                        else
                        {
                            $detail->required = 0;
                        }

                        $detail->repeater = 1;
                        $formTypeR  = FormType::find($detail->form_type_id);
                        $typeR      = $formTypeR->key;
                        $rElements .= '<div class="form-group '.($detail->form_type_id == 13 ? 'd-none' : null) .' '.(isset($detail->class) ? $detail->class : 'col').' mb-7 fv-plugins-icon-container">';
                        $rElements .= '<label class=" '.($detail->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="repeater_'.$detail->column_name.'">'.$detail->title.'</label>';
                        $rElements .= view('crudPackage::formTypes.'. $formTypeR->group,['column' => $detail,'formType' => $formTypeR,'type' => $typeR],compact('crud','language','languageKey'))->render();
                        $rElements .= '</div>';
                    }

                    $elementsView .= '<div data-item-no="0" data-repeater-item>';
                    $elementsView .= '<div class="form-group row">';
                    $elementsView .= '<div class="col-md-1 form-group handle"><i class="bi text-dark me-3 fs-4 bi-arrows-move"></i></div>';
                    $elementsView .= $rElements;
                    $elementsView .= '<div class="col-md-1 form-group"> <a href="javascript:;" data-repeater-delete class="btn btn-flex btn-tertiary mt-6"> <i class="ki-outline ki-trash  fs-3"></i> Sil </a> </div>';
                    $elementsView .= '</div>';
                    $elementsView .= '</div>';
                    $elements     .= view('crudPackage::formTypes.'. $formType->group,['elements' => $elementsView],compact('column','type','crud','formType','language','languageKey'))->render();
                }
            }
            else
            {
                $details = json_decode($column->detail);

                if (count((array)$details) > 0)
                {
                    $info = isset($details->info) ? ' (<small>' .$details->info. '</small>)' : null;

                    if (isset($details->type) && $details->type == 'belongsToMany' && !empty($languageKey))
                    {
                        break;
                    }
                }

                $elements .= '<div class="form-group '.($column->form_type_id == 13 ? 'd-none' : null) .' col-12 mb-7 fv-plugins-icon-container">';
                $elements .= '<label class=" '.($column->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="'.$column->column_name.$languageForAttr.'">'.$column->title.$languageTitle.$info.'</label>';
                $elements .= view('crudPackage::formTypes.'. $formType->group,compact('column','type','crud','formType','language','languageKey','value','copy','originalValue'))->render();
                $elements .= '</div>';
            }
        }

        return $elements;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $crud              = $this->crud;
        $datatableColumns  = [];
        $elements          = '';
        $elementTabs       = [];

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

        return view('crudPackage::modules.create',compact('crud','datatableColumns','elements','elementTabs'));
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
                $rules[$columnName] = ['required',explode('|',$details->validation)];
            }
            else if ($column->required == 0 && isset($details->validation))
            {
                $rules[$columnName] = [explode('|',$details->validation)];
            }
            else if ($column->required == 1 && !isset($details->validation))
            {
                $rules[$columnName] = ['required'];
            }

            if ($status == 1 && isset($details->validation) && strstr($details->validation,'unique'))
            {
                $validations = $details->validation;
                $exxploded   = explode('|',$validations);

                if (count($exxploded) > 1)
                {
                    $uniqueValidateIndex = '';

                    foreach ($exxploded as $exxplodeKey => $exxplode)
                    {
                        if (strstr($exxplode,'unique'))
                        {
                            $uniqueValidateIndex = $exxplodeKey;
                        }
                    }

                    $exxploded[$uniqueValidateIndex] = $exxploded[$uniqueValidateIndex]. ','.$columnName.',' . $data->id;
                    $rules[$columnName]              = $exxploded;
                }
                else
                {
                    $rules[$columnName] = [$details->validation. ','.$columnName.',' . $data->id];
                }
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
                array_push($rules[$columnName],'min:1');

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

            if ($column->form_type_id == 6 && isset($data) && !empty($data->$columnName))
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
            $formType = $column->form_type_id;
            $details  = json_decode($column->detail,true);

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
                $allData[$column->column_name] = json_encode($allData[$column->column_name]);
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

                if (isset($copyValue))
                {
                    if ($formType == 5 || $formType == 6)
                    {
                        $imageUpload = new ImageUpload();

                        if (isset($details['multiple']) && $details['multiple'] == true)
                        {
                            $files = [];

                            foreach(json_decode($copyValue) as $order => $oldFile)
                            {
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
                        if (isset($data->$inputName))
                        {
                            $merge               = array_merge($images,json_decode($data->$inputName,true));
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try
        {
            $copyData = null;
            $status   = 0;
            $crud     = $this->crud;
            $allData  = $request->all();
            $allFiles = $request->allFiles();

            if (isset($allData['crud_copy_id']))
            {
                $model    = $crud->model;
                $copyData = $model::find($allData['crud_copy_id']);
            }

            $validators = $this->dynamicValidation($request,$status,$copyData);
            $validator  = Validator::make($request->all(), $validators[1]);
            $validator->setAttributeNames($validators[0]);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result'  => 2,
                        'message' => $validator->errors()
                    ],403
                );
            }

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

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => $request->has('other_route') ? $request->get('other_route') : route($crud->slug .'.index')
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try
        {
            $crud       = $this->crud;
            $model      = $crud->model;
            $data       = $model::find($id);
            $allFiles   = $request->allFiles();
            $allData    = $request->all();

            $validators = $this->dynamicValidation($request,1,$data);
            $validator  = Validator::make($request->all(), $validators[1]);

            $validator->setAttributeNames($validators[0]);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result'  => 2,
                        'message' => $validator->errors()
                    ],403
                );
            }

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

            $route = $crud->only_edit == 1 ? route($crud->slug.'.edit',$data->id) : route($crud->slug .'.index');

            self::cacheClear();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => $request->has('other_route') ? $request->get('other_route') : $route
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
    public function show(string $id)
    {
        $crud        = $this->crud;
        $elementTabs = [];
        $values      = [];
        $inputValue  = '';

        if (count($this->languages) > 0)
        {
            foreach ($this->languages as $languageKey => $language)
            {
                $elements                     = $this->datatable($language->code,$id);
                $elementTabs[$language->code] = $elements->getData()->data[0];
            }
        }
        else
        {
            $elements = $this->datatable(null,$id);
            $values   = $elements->getData()->data[0];
        }

        return view('crudPackage::modules.show',compact('crud','values','inputValue','elementTabs'));
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
        return view('crudPackage::modules.copy',$this->getEdit($id,1));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('crudPackage::modules.edit',$this->getEdit($id));
    }

    /**
     * Remove the specified resource from storage.
     */
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

                        if (count($this->languages) > 0)
                        {
                            $languageValuesMultiple = DataTranslate::where('model',$crud->model)->where('foreign_key',$id)->where('column_name',$column->column_name)->get();

                            foreach ($languageValuesMultiple as $languageValueMultiple)
                            {
                                $deletedImages = !empty($languageValueMultiple->value) ? array_merge($deletedImages,json_decode($languageValueMultiple->value)) : $deletedImages;
                            }
                        }
                    }
                    else
                    {
                        array_push($deletedImages,$value->{$column->column_name});

                        if (count($this->languages) > 0)
                        {
                            $languageValues = DataTranslate::where('model',$crud->model)->where('foreign_key',$id)->where('column_name',$column->column_name)->get();

                            foreach ($languageValues as $languageValue)
                            {
                                array_push($deletedImages,$languageValue->value);
                            }
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

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route($crud->slug .'.index')
                ]
            );
        }
        catch (\Exception $e)
        {
            if ($e->errorInfo()[1] == 1451)
            {
                $message = '<br> İlişkili tablolar:<br> ' . implode(',<br> ', $e->relations());

                return response()->json(
                    [
                        'result'  => 0,
                        'message' => $e->getMessage().' '.$message,
                    ],500);
            }

            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }

    public function datatable($locale = null,$id = null)
    {
        try
        {
            $crud      = $this->crud;
            $model     = $crud->model;
            $slug      = $crud->slug;
            $modelName = explode('\\', $model);
            $modelName = end($modelName);
            $columns   = isset($id) ? $crud->readColumns : $crud->browseColumns;
            $area1     = isset($crud->area_1) ? json_decode($crud->area_1) : null;
            $dtColumns = [];
            $rawColumns = ['actions'];

            if (isset($id))
            {
                $sql = count($this->relationshipNames) > 0 ? $model::with($this->relationshipNames)->where($crud->table_name .'.id',$id) : $model::where($crud->table_name .'.id',$id);
            }
            else
            {
                $sql = count($this->relationshipNames) > 0 ? $model::with($this->relationshipNames) : $model::select('*');
            }

            if (isset($area1) && isset($area1->order_column_name) && empty($id))
            {
                $sql->orderByTranslate($area1->order_column_name,$area1->order_direction,$locale);
            }

            $data = $sql->get();

            foreach ($columns as $column)
            {
                $details    = json_decode($column->detail,true);
                $columnName = $column->column_name;
                $formType   = $column->form_type_id;

                if (isset($details['realtime']) && empty($id))
                {
                    $formType  = $column->type;
                    $type      = $formType->key;

                    $dtColumns[$columnName] = function ($value) use ($columnName,$details,$formType,$type,$crud,$column,$locale)
                    {
                        $value       = isset($locale) ? $value->getTranslate($locale) : $value;
                        $dt          = 1;
                        $language    = isset($locale) ? multipleLanguages(1,$locale) : null;
                        $languageKey = isset($locale) ? multipleLanguages(2,$locale) : null;

                        return view('crudPackage::formTypes.'. $formType->group,compact('column','type','crud','value','formType','dt','language','languageKey'))->render();
                    };
                }
                else
                {
                    $dtColumns[$columnName] = function ($value) use ($columnName,$locale)
                    {
                        $value = isset($locale) ? $value->getTranslate($locale) : $value;

                        return $value->$columnName;
                    };

                    if ($formType == 15)
                    {
                        $dtColumns[$columnName] = function ($value) use ($columnName,$details,$locale)
                        {
                            $value = isset($value) ? ( isset($locale) ? $value->getTranslate($locale) : $value ) : null;

                            return $value->$columnName == 1 ? '<span class="badge badge-lg badge-success">'. $details['on'] .'</span>' :  '<span class="badge badge-lg badge-danger">'. $details['off'] .'</span>';
                        };
                    }
                    else if ($formType == 12)
                    {
                        $dtColumns[$columnName] = function ($value) use ($columnName,$details,$locale)
                        {
                            $value = isset($value) ? ( isset($locale) ? $value->getTranslate($locale) : $value ) : null;

                            foreach ($details['items'] as $keyItem => $item)
                            {
                                if ($keyItem == $value->$columnName)
                                {
                                    return $item;
                                }
                            }
                        };
                    }
                    else if ($formType == 1)
                    {
                        $dtColumns[$columnName] = function ($value) use ($columnName,$details,$locale)
                        {
                            $value  = isset($value) ? ( isset($locale) ? $value->getTranslate($locale) : $value ) : null;
                            $values = json_decode($value->$columnName);
                            $badges = '';

                            if (is_array($values))
                            {
                                foreach ($values as $item)
                                {
                                    $badges .= '<span class="badge badge-primary me-1">'. $item .'</span>';
                                }

                                return $badges;
                            }
                            else
                            {
                                return $value->$columnName;
                            }
                        };
                    }

                    if ($column->relationship == 0 && count($details) > 0)
                    {
                        foreach ($details as $detailKey => $detail)
                        {
                            if ($detailKey == 'format')
                            {
                                $dtColumns[$columnName] = function ($value) use ($detail,$columnName,$locale)
                                {
                                    $value  = isset($value) ? ( isset($locale) ? $value->getTranslate($locale) : $value ) : null;

                                    return Carbon::parse($value->$columnName)->format($detail);
                                };
                            }
                        }
                    }
                    else if ($column->relationship == 1)
                    {
                        $relationship = CrudRelationships::generateName($model, $column->column_name);

                        $dtColumns[$columnName] = function ($columnValue) use ($relationship, $columnName, $details, $locale)
                        {
                            $value = isset($locale) ? $columnValue->getTranslate($locale) : $columnValue;

                            if (!$value)
                            {
                                return null;
                            }

                            $showColumn = $details['show_column'];

                            if (!empty($details['multiple']) && $details['multiple'] === true)
                            {
                                $values     = json_decode($value->{$columnName}, true) ?? [];
                                $showValues = [];

                                if ($details['type'] == 'belongsToMany' && isset($columnValue))
                                {
                                    $values = isset($locale) && multipleLanguages(2,$locale) > 0 ? $columnValue->{$relationship} : $value->{$relationship};

                                    foreach ($values as $item)
                                    {
                                        $translated   = $locale ? $item->getTranslate($locale) : $item;
                                        $showValues[] = $translated->{$showColumn};
                                    }
                                }
                                else
                                {
                                    foreach ($values as $item)
                                    {
                                        $query = $details['model']::where($details['match_column'],$item)->first();

                                        if ($query)
                                        {
                                            $translated   = $locale ? $query->getTranslate($locale) : $query;
                                            $showValues[] = $translated->{$showColumn};
                                        }
                                    }
                                }

                                return implode(', ', $showValues);
                            }

                            $relationModel = $value->{$relationship};


                            if (!$relationModel)
                            {
                                return null;
                            }

                            $translated = $locale ? $relationModel->getTranslate($locale) : $relationModel;

                            return $translated->{$showColumn};
                        };
                    }
                }
            }

            $datatable = Datatables::of($data);

            foreach ($dtColumns as $name => $callback)
            {
                $datatable->addColumn($name, $callback);
            }

            if (isset($area1) && $area1->order_column_name)
            {
                $datatable->addColumn('orderable',function ($value)
                {
                    return '<i data-id="'. $value->id .'" class="bi text-dark fs-3 bi-arrows-move cursor-move"></i>';
                });

                array_push($rawColumns,'orderable');
            }

            $datatable->addColumn('actions', function ($value) use ($slug, $modelName)
            {
                if (auth()->user()->hasPermission($slug . '.destroy') || auth()->user()->hasPermission($slug . '.edit') || auth()->user()->hasPermission($slug . '.show'))
                {
                    $actions  = '<a href="#" class="btn btn-sm btn-light btn-active-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-target="action-'.$value->id.'"> Aksiyon <i class="ki-duotone ki-down fs-5 ms-1"></i> </a>';
                    $actions .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-primary fw-semibold fs-7 w-125px py-4"  data-kt-menu="true" id="action-'.$value->id.'">';

                    if (auth()->user()->hasPermission($slug . '.create'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="' . route($slug . '.copy', $value->id) . '" class="menu-link px-3"> Kopyala </a> </div>';
                    }

                    if (auth()->user()->hasPermission($slug . '.show'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="' . route($slug . '.show', $value->id) . '" class="menu-link px-3"> Detay </a> </div>';
                    }

                    if (auth()->user()->hasPermission($slug . '.edit'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="'.route($slug . '.edit',$value->id).'" class="menu-link px-3"> Düzenle </a> </div>';
                    }

                    if (auth()->user()->hasPermission($slug . '.destroy'))
                    {
                        $actions .= '<div class="menu-item px-3"> <a href="#" data-title="Bu veriyi" data-route="'.route($slug . '.destroy',$value->id).'" class="menu-link px-3" onclick="destroy(this)"> Sil </a> </div>';
                    }

                    $actions .= '</div>';
                }
                else
                {
                    $actions = '';
                }

                return $actions;
            });
            $datatable->rawColumns(array_merge(array_keys($dtColumns), $rawColumns));

            return $datatable->toJson();
        }
        catch (\Exception $e)
        {
            dd($e);
            return response()->json(['result' => 0,'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'],403);
        }
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

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.'
                ]
            );
        }
        catch (\Exception $e)
        {
            return response()->json(['result' => 0,'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'],403);
        }
    }

    public function orderable(Request $request,$locale = null)
    {
        $crud       = $this->crud;
        $model      = $crud->model;
        $data       = $model::get();
        $columnName = $request->get('order_column_name');
        $direction  = $request->get('order_direction');

        foreach ($data as $value)
        {
            if (isset($locale))
            {
                $translate = DataTranslate::where('foreign_key',$value->id)->where('model',$crud->model)->where('locale',$locale)->where('column_name',$columnName)->first();

                if (empty($translate))
                {
                    $translate              = new DataTranslate();
                    $translate->model       = $crud->model;
                    $translate->column_name = $columnName;
                    $translate->locale      = $locale;
                    $translate->foreign_key = $value->id;
                }
            }

            foreach ($request->order as $order)
            {
                if ($direction == 'asc')
                {
                    $position = $order['position'];
                }
                else
                {
                    $position = (count($request->order) - $order['position']) + 1;
                }

                if ($order['id'] == ($value->foreign_key ?? $value->id))
                {
                    if (isset($locale))
                    {
                        $translate->value = $position;
                        $translate->save();
                    }
                    else
                    {
                        $value->{$columnName} = $position;
                        $value->save();
                    }
                }
            }
        }

        return response()->json([
            'result' => 1,
        ]);
    }

    public function realtime(Request $request, string $id)
    {
        try
        {
            $crud          = $this->crud;
            $model         = $crud->model;
            $data          = $model::find($id);
            $allData       = $request->all();
            $newValue      = null;
            $locale        = null;
            $languageOrder = 0;
            $columnName    = $allData['column_name'];

            if (count($this->languages) > 0 && strstr($columnName,'['))
            {
                $columnName    = explode('[',$columnName);
                $locale        = $columnName[0];
                $columnName    = rtrim($columnName[1],']');
                $languageOrder = multipleLanguages(2,$locale);
            }

            $column   = $crud->getColumn($columnName);
            $formType = $column->form_type_id;
            $details  = json_decode($column->detail,true);

            if ($formType == 15)
            {
                $columnValue = $allData['value'];

                if ($details['on'] ==  $columnValue)
                {
                    $newValue = 1;
                }
                else if ($details['off'] ==  $columnValue)
                {
                    $newValue = 0;
                }
            }
            else
            {
                $newValue = $allData['value'];
            }

            unset($allData['_token']);

            if (!empty($locale) && $languageOrder > 0)
            {
                $translate = DataTranslate::where('model',$crud->model)->where('locale',$locale)->where('foreign_key',$id)->where('column_name',$columnName)->first();

                if (empty($translate))
                {
                    $translate              = new DataTranslate();
                    $translate->model       = $crud->model;
                    $translate->column_name = $columnName;
                    $translate->locale      = $locale;
                    $translate->foreign_key = $id;
                }

                $translate->value = $newValue;
                $translate->save();
            }
            else
            {
                $data->$columnName = $newValue;
                $data->save();
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
}
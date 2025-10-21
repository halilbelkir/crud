<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\Crud;
use Carbon\Carbon;
use crudPackage\Library\ImageUpload\ImageUpload;
use crudPackage\Library\Relationships\CrudRelationships;
use crudPackage\Models\FormType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Session;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class ModuleController extends Controller
{
    public $slugPrefix;
    public $crud;
    public $relationshipNames;
    public $relationShips;

    public $formTypes;


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

        $this->crud          = Crud::where('slug', $this->slugPrefix)->first();
        $this->relationShips = new CrudRelationships($this->crud);

        $this->relationShips->create();

        $this->relationshipNames = $this->relationShips->getRelationshipNames();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $crud  = $this->crud;
        $area1 = isset($crud->area_1) ? json_decode($crud->area_1) : null;

        return view('crudPackage::modules.index',compact('crud','area1'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $crud              = $this->crud;
        $datatableColumns  = [];
        $relationships     = $this->relationShips;
        $elements          = '';

        foreach($crud->addColumns as $column)
        {
            $formType  = $column->type;
            $type      = $formType->key;
            $info      = '';

            if ($column->repeater == 1)
            {
                $details      = json_decode($column->detail);
                $rElements    = '';
                $elementsView = '';

                foreach($details as $detail)
                {
                    if (strstr('required',$detail->validation))
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
                    $rElements .= '<label class=" '.($column->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="repeater_'.$detail->column_name.'">'.$detail->title.'</label>';
                    $rElements .= view('crudPackage::formTypes.'. $formTypeR->group,['column' => $detail,'formType' => $formTypeR,'type' => $typeR],compact('crud'))->render();
                    $rElements .= '</div>';
                }

                $elementsView .= '<div data-item-no="0" data-repeater-item>';
                $elementsView .= '<div class="form-group row">';
                $elementsView .= '<div class="col-md-1 form-group handle"><i class="bi text-dark me-3 fs-4 bi-arrows-move"></i></div>';
                $elementsView .= $rElements;
                $elementsView .= '<div class="col-md-1 form-group"> <a href="javascript:;" data-repeater-delete class="btn btn-flex btn-tertiary mt-6"> <i class="ki-outline ki-trash  fs-3"></i> Sil </a> </div>';
                $elementsView .= '</div>';
                $elementsView .= '</div>';
                $elements     .= view('crudPackage::formTypes.'. $formType->group,['elements' => $elementsView],compact('column','type','crud','formType'))->render();
            }
            else
            {
                if (isset($column->detail))
                {
                    $details = json_decode($column->detail);
                    $info    = isset($details->info) ? ' (<small>' .$details->info. '</small>)' : null;
                }

                $elements .= '<div class="form-group '.($column->form_type_id == 13 ? 'd-none' : null) .' col-12 mb-7 fv-plugins-icon-container">';
                $elements .= '<label class=" '.($column->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="'.$column->column_name.'">'.$column->title.$info.'</label>';
                $elements .= view('crudPackage::formTypes.'. $formType->group,compact('column','type','crud','formType'))->render();
                $elements .= '</div>';
            }
        }

        return view('crudPackage::modules.create',compact('crud','datatableColumns','relationships','elements'));
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

        if ($request->has('crud_copy_id'))
        {
            $status = 1;
        }

        foreach ($columns as $column)
        {
            $details                = json_decode($column->detail);
            $columnName             = $column->column_name;
            $attribute[$columnName] = $column->title;


            if ($column->required == 1 && isset($details->validation))
            {
                $rules[$columnName] = 'required|'.$details->validation;
            }
            else if ($column->required == 0 && isset($details->validation))
            {
                $rules[$columnName] = $details->validation;
            }
            else if ($column->required == 1 && !isset($details->validation))
            {
                $rules[$columnName] = 'required';
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

            if ($column->repeater == 1)
            {
                $rules[$columnName] .= '|array|min:1';

                foreach ($details as $detail)
                {
                    $rules[$columnName.'.*.'.$detail->column_name]     = $detail->validation;
                    $attribute[$columnName.'.*.'.$detail->column_name] = $detail->title;
                }
            }

            if ($column->form_type_id == 6 && isset($data) && !empty($data->$columnName))
            {
                unset($rules[$columnName]);
            }
        }

        return [$attribute,$rules];
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
            $allFiles = $request->allFiles();
            $allData  = $request->all();
            $images   = [];

            if (isset($allData['crud_copy_id']))
            {
                $model    = $crud->model;
                $copyData = $model::find($allData['crud_copy_id']);
                $status   = 1;
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

            foreach ($crud->addColumns as $columnKey => $column)
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

            if (count($allFiles) > 0)
            {
                $imageUpload = new ImageUpload();

                foreach ($allFiles as $inputName => $file)
                {
                    if (is_array($file))
                    {
                        foreach($file as $order => $mFile)
                        {
                            $name      = $crud->table_name.'-'.$order.'-'.random_int(100000000, 999999999999).time();
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
                            $allData[$inputName] = json_encode($images,JSON_UNESCAPED_UNICODE,true);
                        }
                    }
                    else
                    {
                        $name                = $crud->table_name.'-'.random_int(100000000, 999999999999).time();
                        $fileName             = $imageUpload->getName($file,$name,$crud->table_name);
                        $allData[$inputName] = $fileName;
                    }
                }
            }

            unset($allData['crud_copy_id']);

            $data = new $crud->model();

            foreach ($allData as $key => $value)
            {
                $data->$key = $value;
            }

            $data->save();

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $crud      = $this->crud;
        $elements  = $this->datatable($id);
        $values    = $elements->getData()->data[0];
        $inputValue = '';

        return view('crudPackage::modules.show',compact('crud','values','inputValue'));
    }

    public function getEdit(string $id)
    {
        $crud              = $this->crud;
        $model             = $crud->model;
        $value             = $model::find($id);
        $elements          = '';
        $breadcrumbs       =
            [
                'activePage'      => $crud->display_single.' Düzenle',
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

        foreach($crud->editColumns as $columnKey => $column)
        {
            $formType  = $column->type;
            $type      = $formType->key;
            $info      = '';

            if ($column->repeater == 1)
            {
                $details       = json_decode($column->detail);

                if (isset($value->{$column->column_name}))
                {
                    $elementValues = json_decode($value->{$column->column_name});
                    $elementsView      = '';
                    $repeaterValue     = [];

                    foreach($elementValues as $elementKey => $elementValue)
                    {
                        $rElements    = '';

                        foreach ($details as $detail)
                        {
                            if (strstr('required',$detail->validation))
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
                            $rElements .= '<label class=" '.($column->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="repeater_'.$detail->column_name.'">'.$detail->title.'</label>';
                            $rElements .= view('crudPackage::formTypes.'. $formTypeR->group,['column' => $detail,'formType' => $formTypeR,'type' => $typeR,'value' => $elementValue],compact('crud'))->render();
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

                    $elements .= view('crudPackage::formTypes.'. $formType->group,['elements' => $elementsView],compact('column','type','crud','formType'))->render();
                }
                else
                {
                    $details      = json_decode($column->detail);
                    $rElements    = '';
                    $elementsView = '';

                    foreach($details as $detail)
                    {
                        if (strstr('required',$detail->validation))
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
                        $rElements .= '<label class=" '.($column->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="repeater_'.$detail->column_name.'">'.$detail->title.'</label>';
                        $rElements .= view('crudPackage::formTypes.'. $formTypeR->group,['column' => $detail,'formType' => $formTypeR,'type' => $typeR],compact('crud'))->render();
                        $rElements .= '</div>';
                    }

                    $elementsView .= '<div data-item-no="0" data-repeater-item>';
                    $elementsView .= '<div class="form-group row">';
                    $elementsView .= '<div class="col-md-1 form-group handle"><i class="bi text-dark me-3 fs-4 bi-arrows-move"></i></div>';
                    $elementsView .= $rElements;
                    $elementsView .= '<div class="col-md-1 form-group"> <a href="javascript:;" data-repeater-delete class="btn btn-flex btn-tertiary mt-6"> <i class="ki-outline ki-trash  fs-3"></i> Sil </a> </div>';
                    $elementsView .= '</div>';
                    $elementsView .= '</div>';
                    $elements     .= view('crudPackage::formTypes.'. $formType->group,['elements' => $elementsView],compact('column','type','crud','formType'))->render();
                }
            }
            else
            {
                if (isset($column->detail))
                {
                    $details = json_decode($column->detail);
                    $info    = isset($details->info) ? ' (<small>' .$details->info. '</small>)' : null;
                }

                $elements .= '<div class="form-group '.($column->form_type_id == 13 ? 'd-none' : null) .' col-12 mb-7 fv-plugins-icon-container">';
                $elements .= '<label class=" '.($column->required == 1 ? 'required' : null) .' w-100 fw-semibold fs-6 mb-2" for="'.$column->column_name.'">'.$column->title.$info.'</label>';
                $elements .= view('crudPackage::formTypes.'. $formType->group,compact('column','type','crud','value','formType'))->render();
                $elements .= '</div>';
            }
        }

        return [
            'crud'        => $crud,
            'elements'    => $elements,
            'value'       => $value,
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    public function copy(string $id)
    {
        return view('crudPackage::modules.copy',$this->getEdit($id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('crudPackage::modules.edit',$this->getEdit($id));
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
            $images     = [];
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


            if (count($allFiles) > 0)
            {
                $imageUpload = new ImageUpload();

                foreach ($allFiles as $inputName => $file)
                {
                    if (is_array($file))
                    {
                        foreach($file as $order => $mFile)
                        {
                            $name      = $crud->table_name.'-'.$order.'-'.random_int(100000000, 999999999999).time();
                            $fileName   = $imageUpload->getName($mFile,$name,$crud->table_name);
                            $images[]  = $fileName;
                        }

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
                    else
                    {
                        $imageUpload->delete($data->$inputName);
                        $name                = $crud->table_name.'-'.random_int(100000000, 999999999999).time();
                        $fileName             = $imageUpload->getName($file,$name,$crud->table_name);
                        $allData[$inputName] = $fileName;
                    }
                }
            }

            foreach ($crud->editColumns as $columnKey => $column)
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
            }

            unset($allData['_method']);

            foreach ($allData as $key => $value)
            {
                $data->$key = $value;
            }

            $data->save();

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try
        {
            $crud        = $this->crud;
            $model       = $crud->model;
            $value       = $model::find($id);
            $imageUpload = new ImageUpload();
            $formTypeIds = [5,6];

            foreach ($crud->items as $columnKey => $column)
            {
                if (in_array($column->form_type_id,$formTypeIds))
                {

                    $details  = json_decode($column->detail,true);

                    if (isset($details['multiple']) && $details['multiple'] == true)
                    {
                        $imageUpload->delete(json_decode($value->{$column->column_name}));
                    }
                    else
                    {
                        $imageUpload->delete($value->{$column->column_name});
                    }
                }
            }

            $value->delete();

            self::cacheClear();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route($crud->slug .'.index')
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

    public function datatable($id = null)
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
            $sql = count($this->relationshipNames) > 0 ? $model::with($this->relationshipNames)->where('id',$id) : $model::where('id',$id);
        }
        else
        {
            $sql = count($this->relationshipNames) > 0 ? $model::with($this->relationshipNames) : $model::select('*');
        }

        if (isset($area1) && isset($area1->order_column_name))
        {
            $sql->orderBy($area1->order_column_name,$area1->order_direction);
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

                $dtColumns[$columnName] = function ($value) use ($columnName,$details,$formType,$type,$crud,$column)
                {
                    $dt = 1;

                    return view('crudPackage::formTypes.'. $formType->group,compact('column','type','crud','value','formType','dt'))->render();
                };
            }
            else
            {
                if ($formType == 15)
                {
                    $dtColumns[$columnName] = function ($value) use ($columnName,$details)
                    {
                        return $value->$columnName == 1 ? '<span class="badge badge-lg badge-success">'. $details['on'] .'</span>' :  '<span class="badge badge-lg badge-danger">'. $details['off'] .'</span>';
                    };
                }


                if ($formType == 12)
                {
                    $dtColumns[$columnName] = function ($value) use ($columnName,$details)
                    {
                        foreach ($details['items'] as $keyItem => $item)
                        {
                            if ($keyItem == $value->$columnName)
                            {
                                return $item;
                            }
                        }
                    };
                }

                if ($formType == 1)
                {
                    $dtColumns[$columnName] = function ($value) use ($columnName,$details)
                    {
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
                            $dtColumns[$columnName] = function ($value) use ($detail,$columnName)
                            {
                                return Carbon::parse($value->$columnName)->format($detail);
                            };
                        }
                    }
                }
                else if ($column->relationship == 1)
                {
                    $relationship = $this->relationShips->nameGenerate($column->column_name);

                    $dtColumns[$columnName] = function ($value) use ($relationship,$columnName,$details)
                    {
                        $showColumn = $details['show_column'];

                        return $value->$relationship->$showColumn;
                    };
                }
                else
                {
                    $dtColumns[$columnName] = function ($value) use ($columnName)
                    {
                        return $value->$columnName;
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
                return '<i class="bi text-dark fs-3 bi-arrows-move cursor-move"></i>';
            });

            array_push($rawColumns,'orderable');
        }

        $datatable->addColumn('actions', function ($value) use ($slug, $modelName)
        {
            if (auth()->user()->hasPermission($slug . '.destroy') || auth()->user()->hasPermission($slug . '.edit') || auth()->user()->hasPermission($slug . '.show'))
            {
                $actions  = '<a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-target="action-'.$value->id.'"> Aksiyon <i class="ki-duotone ki-down fs-5 ms-1"></i> </a>';
                $actions .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"  data-kt-menu="true" id="action-'.$value->id.'">';

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
                    $actions .= '<div class="menu-item px-3"> <a href="#" data-title="'.$value->title.' isimli veriyi" data-route="'.route($slug . '.destroy',$value->id).'" class="menu-link px-3" onclick="destroy(this)"> Sil </a> </div>';
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

    public function fileDestroy($id,$order,$columnName)
    {
        try
        {
            $crud        = $this->crud;
            $model       = $crud->model;
            $data        = $model::find($id);
            $imageUpload = new ImageUpload();
            $files        = [];

            foreach (json_decode($data->$columnName) as $key =>  $fil)
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

            $data->$columnName = json_encode($files);
            $data->save();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.'
                ]
            );
        }
        catch (Exception $e)
        {
            return response()->json(['result' => 0,'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'],403);
        }
    }

    public function orderable(Request $request)
    {
        $crud       = $this->crud;
        $model      = $crud->model;
        $data       = $model::get();
        $columnName = $request->get('order_column_name');
        $direction  = $request->get('order_direction');

        foreach ($data as $value)
        {
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

                if ($order['id'] == $value->id)
                {
                    $value->{$columnName} = $position;
                    $value->save();
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
            $crud     = $this->crud;
            $model    = $crud->model;
            $data     = $model::find($id);
            $allData  = $request->all();
            $newData  = [];
            $column   = $allData['column_name'];
            $column   = $crud->getColumn($column);
            $formType = $column->form_type_id;
            $details  = json_decode($column->detail,true);

            if ($formType == 15)
            {
                $columnValue = $allData['value'];

                if ($details['on'] ==  $columnValue)
                {
                    $newData[$column->column_name] = 1;
                }
                else if ($details['off'] ==  $columnValue)
                {
                    $newData[$column->column_name] = 0;
                }
            }
            else
            {
                $newData[$column->column_name] = $allData['value'];
            }

            unset($allData['_token']);

            foreach ($newData as $key => $value)
            {
                $data->$key = $value;
            }

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
}
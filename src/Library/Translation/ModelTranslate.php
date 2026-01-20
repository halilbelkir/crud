<?php

namespace crudPackage\Library\Translation;

use crudPackage\Library\Relationships\CrudRelationships;
use crudPackage\Models\Crud;
use crudPackage\Models\DataTranslate;
use Illuminate\Database\Eloquent\Model;
class ModelTranslate
{
    protected array $data;
    protected $model;
    protected string $locale;
    public function __construct($model, string $locale)
    {
        $this->model   = $model;
        $this->locale  = $locale;
        $base          = $model->getAttributes();
        $relationNames = CrudRelationships::getModelRelations(get_class($this->model));

        if ($relationNames && multipleLanguages(2, $locale) > 0)
        {
            $crud         = Crud::where('model',get_class($this->model))->first();
            $relations    = $crud->getRelationships;
            $translations = DataTranslate::where('model', get_class($model))
                            ->where('foreign_key', $model->getKey())
                            ->where('locale', $locale)
                            ->pluck('value','column_name')
                            ->toArray();

            foreach ($relations as $relation)
            {
                $detail        = json_decode($relation->detail);
                $name          = CrudRelationships::generateName(get_class($this->model), $relation->column_name);
                $relationModel = $detail->model;

                if (isset($translations[$relation->column_name]))
                {
                    $newRelation         = $relationModel::where($detail->match_column, $translations[$relation->column_name])->select($detail->show_column)->first();
                    $translations[$name] = $newRelation;
                }
            }
        }
        else
        {
            $translations = DataTranslate::where('model', get_class($model))
                            ->where('foreign_key', $model->getKey())
                            ->where('locale', $locale)
                            ->pluck('value','column_name')
                            ->toArray();
        }

        $this->data = array_replace($base, $translations);
    }

    public function __get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
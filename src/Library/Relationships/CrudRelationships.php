<?php

namespace crudPackage\Library\Relationships;

use Illuminate\Database\Eloquent\Model;

class CrudRelationships
{
    private $crud;
    private $relationshipNames = [];

    public function __construct($crud)
    {
        $this->setCrud($crud);
    }

    public  function create()
    {
        $crud      = $this->getCrud();
        $relations = $crud->getRelationships;

        foreach ($relations as $relation)
        {
            $detail  = json_decode($relation->detail);
            $name    = $this->nameGenerate($relation->column_name);
            $model   = $crud->model;
            $type    = $detail->type;
            $related = $detail->model;

            $this->setRelationshipNames($name);

            $model::resolveRelationUsing($name, function ($instance) use ($type, $related, $detail)
            {
                return $instance->{$type}($related, $detail->column_name, $detail->match_column);
            });
        }
    }


    public function nameGenerate($columnName)
    {
        $crud = $this->getCrud();

        return $crud->slug.'_'.$columnName.'_relationship';
    }


    /**
     * @return mixed
     */
    public function getRelationshipNames()
    {
        return $this->relationshipNames;
    }

    /**
     * @param mixed $relationshipNames
     */
    private function setRelationshipNames($relationshipNames): void
    {
        $this->relationshipNames[] = $relationshipNames;
    }

    /**
     * @return mixed
     */
    private function getCrud()
    {
        return $this->crud;
    }

    /**
     * @param mixed $crud
     */
    private function setCrud($crud): void
    {
        $this->crud = $crud;
    }
}
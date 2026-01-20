<?php

namespace crudPackage\Library\Relationships;

class CrudRelationships
{
    protected static array $registry = [];
    protected static array $slugRegistry = [];

    private $crud;

    public function __construct($crud)
    {
        $this->crud = $crud;
    }

    public function create(): void
    {
        $crud      = $this->crud;
        $relations = $crud->getRelationships;
        $model     = $crud->model;

        if (!$relations || $relations->count() === 0)
        {
            return;
        }

        self::$slugRegistry[$model] = $crud->slug;

        foreach ($relations as $relation)
        {
            $detail  = json_decode($relation->detail);
            $name    = self::generateName($model, $relation->column_name);
            $type    = $detail->type;
            $related = $detail->model;

            if (in_array($name, self::$registry[$model] ?? [], true))
            {
                continue;
            }

            self::$registry[$model][] = $name;

            $model::resolveRelationUsing($name, function ($instance) use ($type, $related, $detail)
            {
                return $instance->{$type}(
                    $related,
                    $detail->column_name,
                    $detail->match_column
                );
            });
        }
    }

    public static function getModelRelations(string $model): array
    {
        return self::$registry[$model] ?? [];
    }

    public static function generateName(string $model, string $columnName): string
    {
        $slug = self::$slugRegistry[$model] ?? class_basename($model);

        return $slug . '_' . $columnName . '_relationship';
    }
}

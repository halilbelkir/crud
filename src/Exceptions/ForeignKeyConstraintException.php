<?php

namespace crudPackage\Exceptions;

use RuntimeException;
class ForeignKeyConstraintException extends RuntimeException
{
    protected array $relations = [];
    protected array $errorInfo = [];

    public function __construct(string $message, array $relations = [], array $errorInfo = [])
    {
        parent::__construct($message);

        $this->relations = $relations;
        $this->errorInfo = $errorInfo;
    }

    public function relations(): array
    {
        return $this->relations;
    }

    public function errorInfo(): array
    {
        return $this->errorInfo;
    }
}
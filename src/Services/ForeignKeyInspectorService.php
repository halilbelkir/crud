<?php

namespace crudPackage\Services;
use Illuminate\Support\Facades\DB;

class ForeignKeyInspectorService
{
    public function getDependentTables(string $table, string $column = 'id'): array
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('REFERENCED_TABLE_NAME', $table)
            ->where('REFERENCED_COLUMN_NAME', $column)
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->get(['TABLE_NAME', 'COLUMN_NAME'])
            ->map(fn ($row) => "{$row->TABLE_NAME}.{$row->COLUMN_NAME}")
            ->unique()
            ->values()
            ->toArray();
    }
}
<?php

namespace crudPackage\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use crudPackage\Exports\TableExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class Table
{
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public const EXPORT_FORMATS = ['xlsx', 'csv', 'pdf'];

    public const PDF_LIMIT = 500;

    public const EXPORT_MEMORY = '512M';

    public static function exporting(): bool
    {
        return in_array((string) request('export'), static::EXPORT_FORMATS, true);
    }

    public static function perPage(): int
    {
        $perPage = (int) request('sayfada', 25);

        return in_array($perPage, static::PER_PAGE_OPTIONS, true) ? $perPage : 25;
    }

    public static function sortOptions(array $columns): array
    {
        $options = [];

        foreach ($columns as $column)
        {
            if (is_string($column['sortable'] ?? null))
            {
                $options[$column['sortable']] = $column['label'] ?? $column['sortable'];
            }
        }

        return $options;
    }

    public static function sortable(array $columns): array
    {
        return array_keys(static::sortOptions($columns));
    }

    public static function sortColumn(array $columns): ?string
    {
        $field = (string) request('sirala');

        return in_array($field, static::sortable($columns), true) ? $field : null;
    }

    public static function sortDirection(): string
    {
        return request('yon') === 'desc' ? 'desc' : 'asc';
    }

    public static function sort($records, array $columns)
    {
        $field = static::sortColumn($columns);

        if ($field === null)
        {
            return $records;
        }

        $order = collect($columns)->firstWhere('sortable', $field)['order'] ?? null;

        return is_callable($order)
            ? $order($records->reorder(), static::sortDirection())
            : $records->reorder()->orderBy($field, static::sortDirection());
    }

    public static function export($records, array $columns, string $title): ?Response
    {
        $format = (string) request('export');

        if (! in_array($format, static::EXPORT_FORMATS, true))
        {
            return null;
        }

        ini_set('memory_limit', static::EXPORT_MEMORY);

        $rows     = collect(is_iterable($records) ? $records : $records->get());
        $total    = $rows->count();
        $headings = static::headings($columns);
        $filename = Str::slug($title) . '-' . now()->format('Y-m-d-Hi');

        if ($format === 'pdf')
        {
            if ($total > static::PDF_LIMIT)
            {
                $rows = $rows->take(static::PDF_LIMIT);
            }

            return Pdf::loadView('crudPackage::exports.table',
            [
                'title'    => $title,
                'headings' => $headings,
                'rows'     => static::data($columns, $rows),
                'limited'  => $total > static::PDF_LIMIT ? $total : null,
            ])
                ->setPaper('a4', count($headings) > 4 ? 'landscape' : 'portrait')
                ->download($filename . '.pdf');
        }

        $data = static::data($columns, $rows);

        return Excel::download(new TableExport($headings, $data, $title), $filename . '.' . $format);
    }

    protected static function exportable(array $column): bool
    {
        if (($column['export'] ?? true) === false)
        {
            return false;
        }

        return ! in_array($column['type'] ?? 'text', ['check', 'expand', 'actions'], true);
    }

    protected static function headings(array $columns): array
    {
        $headings = [];

        foreach ($columns as $column)
        {
            if (static::exportable($column))
            {
                $headings[] = $column['label'] ?? '';
            }
        }

        return $headings;
    }

    protected static function data(array $columns, $rows): array
    {
        $data = [];

        foreach ($rows as $row)
        {
            $cells = [];

            foreach ($columns as $column)
            {
                if (static::exportable($column))
                {
                    $cells[] = static::cell($column, $row);
                }
            }

            $data[] = $cells;
        }

        return $data;
    }

    protected static function cell(array $column, $row): string
    {
        $resolve = static function ($key, $default = null) use ($column, $row)
        {
            $value = $column[$key] ?? $default;

            return $value instanceof \Closure ? $value($row) : $value;
        };

        return match ($column['type'] ?? 'text')
        {
            'lead'   => trim($resolve('name') . ($resolve('sub') ? ' (' . $resolve('sub') . ')' : '')),
            'chips'  => collect((array) $resolve('items', []))->map(fn ($chip) => trim(($chip['label'] ?? '') . ' ' . ($chip['value'] ?? '')))->implode(', '),
            default  => (string) $resolve('value', ''),
        };
    }
}

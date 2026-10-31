<?php

namespace crudPackage\Support;

use Collator;

class Sort
{
    public static function options(array $options): array
    {
        $collator = class_exists(Collator::class) ? new Collator('tr_TR') : null;

        uasort($options, function ($first, $second) use ($collator)
        {
            $left  = (string) $first;
            $right = (string) $second;

            return $collator ? $collator->compare($left, $right) : strcasecmp($left, $right);
        });

        return $options;
    }
}

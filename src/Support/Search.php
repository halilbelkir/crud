<?php

namespace crudPackage\Support;

class Search
{
    protected const CONFUSABLES = ['iıİI', 'sşSŞ', 'gğGĞ', 'uüUÜ', 'oöOÖ', 'cçCÇ'];

    public static function pattern(string $term): string
    {
        $clean = str_replace(['%', '_'], '', trim($term));

        foreach (static::CONFUSABLES as $group)
        {
            $clean = preg_replace('/[' . $group . ']/u', '_', $clean);
        }

        return '%' . $clean . '%';
    }

    public static function fold(string $value): string
    {
        $map = [
            'ı' => 'i', 'İ' => 'i', 'I' => 'i',
            'ş' => 's', 'Ş' => 's',
            'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u',
            'ö' => 'o', 'Ö' => 'o',
            'ç' => 'c', 'Ç' => 'c',
        ];

        return mb_strtolower(strtr($value, $map), 'UTF-8');
    }
}

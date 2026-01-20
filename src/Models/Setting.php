<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use crudPackage\Models\Language;

class Setting extends Model
{
    /**
     * languages JSON → HER ZAMAN ARRAY
     */
    protected function languages(): Attribute
    {
        return Attribute::make(
            get: fn ($value) =>
            is_array($value)
                ? $value
                : json_decode($value, true) ?? [],

            set: fn ($value) => !empty($value) ? json_encode(array_values($value) ?? []) : null,
        );
    }

    public function languageModels(): Collection
    {
        return Language::whereIn('id', $this->languages)->get();
    }
}

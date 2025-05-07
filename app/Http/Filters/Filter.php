<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class Filter extends AbstractFilter
{
    protected const SEARCH = 'search';
    protected const CATEGORY_ID = 'category_id';
    protected const RATING = 'rating';
//    protected const IS_A_FREE = 'isAFree';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::CATEGORY_ID => [$this, 'category_id'],
            self::RATING => [$this, 'rating'],
            'isAFree' => [$this, 'isAFree'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        $builder->where(function ($query) use ($value) {
            $query->where('first_name', 'like', '%' . $value . '%');
        });

        $builder->OrWhere(function ($query) use ($value) {
            $query->where('last_name', 'like', '%' . $value . '%');
        });

        $builder->OrWhere(function ($query) use ($value) {
            $query->where('categories.subtitle', 'like', '%' . $value . '%');
        });

        $builder->orderByRaw("CASE
                                WHEN first_name LIKE ? THEN 0
                                WHEN last_name LIKE ? THEN 1
                                WHEN categories.subtitle LIKE ? THEN 2
                                ELSE 3
                              END", ['%' . $value . '%', '%' . $value . '%']);
        // Настроить поиск сначала по имени + фамилии,
        // потом по категории
        // потом по описанию категории
    }

    protected function category_id(Builder $builder, $value)
    {
        $builder->where('category_id', $value);
    }

    protected function rating(Builder $builder, $value)
    {
        //
    }

    protected function isAFree(Builder $builder, $value)
    {
        if ($value) {
            $builder->where('price', '=', 0);
        } else {
            $builder->where('price', '!=', 0);
        }
    }
}

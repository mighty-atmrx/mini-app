<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class Filter extends AbstractFilter
{
    protected const SEARCH = 'search';
    protected const CATEGORY_ID = 'category_id';
    protected const RATING = 'rating';
    protected const MAX_PRICE = 'max_price';
    protected const MIN_PRICE = 'min_price';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::CATEGORY_ID => [$this, 'category_id'],
            self::RATING => [$this, 'rating'],
            self::MAX_PRICE => [$this, 'max_price'],
            self::MIN_PRICE => [$this, 'min_price'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        $builder->where('first_name', $value);
    }

    protected function category_id(Builder $builder, $value)
    {
        $builder->where('category_id', $value);
    }

    protected function rating(Builder $builder, $value)
    {
        //
    }

    protected function max_price(Builder $builder, $value)
    {
        $builder->where('price', '<=', $value);
    }

    protected function min_price(Builder $builder, $value)
    {
        $builder->where('price', '>=', $value);
    }
}

<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class Filter extends AbstractFilter
{
    protected const SEARCH = 'search';
    protected const CATEGORY = 'category';
    protected const RATING = 'rating';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::CATEGORY => [$this, 'category'],
            self::RATING => [$this, 'rating'],
            'isAFree' => [$this, 'isAFree'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        if (empty($value)) {
            return $builder;
        }
        \Log::info('Applying search filter', ['value' => $value]);

        $lowerValue = mb_strtolower($value);
        \Log::info('Lowercased value', ['lowerValue' => $lowerValue]);

        $builder->where(function ($query) use ($lowerValue) {
            $query->whereRaw('LOWER(experts.first_name) LIKE ?', ['%' . $lowerValue . '%'])
                ->orWhereRaw('LOWER(experts.last_name) LIKE ?', ['%' . $lowerValue . '%'])
                ->orWhereHas('categories', function ($categoryQuery) use ($lowerValue) {
                    $categoryQuery->whereRaw('LOWER(categories.title) LIKE ?', ['%' . $lowerValue . '%'])
                        ->orWhereRaw('LOWER(categories.description) LIKE ?', ['%' . $lowerValue . '%']);
                })
            ;
        });

        $builder->orderByRaw(
            "CASE
                    WHEN LOWER(experts.first_name) LIKE ? THEN 0
                    WHEN LOWER(experts.last_name) LIKE ? THEN 1
                    ELSE 2
                 END",
            [strtolower("{$value}%"), strtolower("{$value}%")]
        );

        \Log::info('Query built', ['sql' => $builder->toSql(), 'bindings' => $builder->getBindings()]);
        return $builder;
    }

    protected function category(Builder $builder, $value)
    {
        if (empty($value)) {
            return $builder;
        }

        \Log::info('Applying category filter', ['value' => $value]);
        $lowerValue = mb_strtolower($value);
        \Log::info('Lowercased value', ['lowerValue' => $lowerValue]);

        $builder->where(function ($query) use ($lowerValue) {
            $query->whereHas('categories', function ($categoryQuery) use ($lowerValue) {
                $categoryQuery->whereRaw('LOWER(categories.subtitle) LIKE ?', ['%' . $lowerValue . '%']);
            });
        });

        \Log::info('Query built', ['sql' => $builder->toSql(), 'bindings' => $builder->getBindings()]);
        return $builder;
    }

    protected function rating(Builder $builder, $value)
    {
        if (empty($value)) {
            return $builder;
        }

        \Log::info('Applying rating filter', ['value' => $value]);

        return $builder->where('rating', '>=', $value);
    }

    protected function isAFree(Builder $builder, $value)
    {
        if ($value) {
            $builder->whereHas('services', function ($query) use ($value) {
                $query->where('price', '=', 0);
            });
        } else {
            $builder->whereDoesntHave('services', function ($query) use ($value) {
                $query->where('price', '=', 0);
            });
        }
    }
}

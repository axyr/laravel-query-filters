<?php

namespace Axyr\QueryFilters;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;

/**
 * @method BuilderContract filterBy(QueryFilters $filters)
 */
trait FiltersByQueryfilter
{
    public function scopeFilterBy(BuilderContract $query, QueryFilters $filters): BuilderContract
    {
        return $filters->applyToQuery($query);
    }
}

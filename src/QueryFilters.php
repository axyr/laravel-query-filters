<?php

namespace Axyr\QueryFilters;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ReflectionMethod;

abstract class QueryFilters
{
    protected array $filters = [];

    protected ?BuilderContract $query = null;

    public static function fromArray(array $filters = []): static
    {
        return (new static)->setFilters($filters);
    }

    public static function fromRequest(Request $request): static
    {
        return (new static)->setFilters($request->all());
    }

    public function setFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function addFilters(array $filters): static
    {
        foreach ($filters as $name => $value) {
            $this->addFilter($name, $value);
        }

        return $this;
    }

    public function addFilter(string $name, mixed $value): static
    {
        $this->filters[$name] = $value;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilterValue(string $filterName): mixed
    {
        return $this->filters[$filterName] ?? null;
    }

    public function applyToQuery(BuilderContract $query): BuilderContract
    {
        $this->query = $query;

        foreach ($this->getFilters() as $filter => $value) {
            if ($this->filterCanBeApplied($filter, $value)) {
                $this->{Str::camel($filter)}($value);
            }
        }

        return $query;
    }

    protected function filterCanBeApplied(string $filter, mixed $value): bool
    {
        $method = Str::camel($filter);

        if (!method_exists($this, $method)) {
            return false;
        }

        if ($value !== '' && $value !== null) {
            $data = Arr::only($this->getFilters(), $filter);
            $rules = Arr::only($this->getRules(), $filter);

            return !Validator::make($data, $rules)->fails();
        }

        return (new ReflectionMethod($this, $method))->getNumberOfParameters() === 0;
    }

    protected function getRules(): array
    {
        return [];
    }
}

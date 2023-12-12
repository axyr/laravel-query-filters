<?php

namespace Axyr\QueryFilters\Tests\Dummy;

use Axyr\QueryFilters\QueryFilters;

class Filters extends QueryFilters
{
    public function attributeA($value): void
    {
        $this->query->where('a', $value);
    }

    public function attributeB($value): void
    {
        $this->query->where('b', $value);
    }

    public function attributeC($value): void
    {
        $this->query->where('c', $value);
    }

    public function attributeD($value): void
    {
        $this->query->where('d', $value);
    }

    public function attributeE(): void
    {
        $this->query->where('e', 1);
    }

    protected function getRules(): array
    {
        return [
            'attribute-c' => 'bool',
            'attribute-d' => 'int|digits:2',
        ];
    }
}

<?php

namespace Axyr\QueryFilters\Tests\Dummy;

use Axyr\QueryFilters\FiltersByQueryfilter;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    use FiltersByQueryfilter;
}

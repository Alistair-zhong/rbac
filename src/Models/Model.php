<?php

namespace Rbac\Models;

use Rbac\Traits\ModelHelpers;
use DateTimeInterface;

class Model extends \Jenssegers\Mongodb\Eloquent\Model
{
    use ModelHelpers;

    protected $connection = 'mongodb';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}

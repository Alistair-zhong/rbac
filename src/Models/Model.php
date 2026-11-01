<?php

namespace Rbac\Models;

use Rbac\Traits\ModelHelpers;
use DateTimeInterface;

class Model extends \MongoDB\Laravel\Eloquent\Model
{
    use ModelHelpers;

    protected $connection = 'mongodb';

    protected $primaryKey = '_id';

    // public $incrementing = false;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getRouteKeyName()
    {
        return $this->getKeyName();
    }
}

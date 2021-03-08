<?php

namespace Rbac\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ObjectID implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return $value instanceof \MongoDB\BSON\ObjectID ? (string)$value : $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param array $value
     * @param array $attributes
     *
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        return is_string($value) ? new \MongoDB\BSON\ObjectID($value) : $value;
    }
}

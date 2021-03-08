<?php

namespace Rbac\Traits;

use Rbac\Filters\Filter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Builder;

/**
 * 通用模型中的方法
 *
 * Trait ModelHelpers
 * @package App\Admin\Traits
 */
trait ModelHelpers
{
    /**
     * 最大每页数，避免瞎搞的人
     *
     * @var int
     */
    protected $maxPerPage = 28;

    public function getPerPage()
    {
        $perPage = Request::get('per_page');
        $intPerPage = (int) $perPage;
        if (($intPerPage > 0) && ((string) $intPerPage === $perPage)) {
            return min($intPerPage, $this->maxPerPage);
        } else {
            return $this->perPage;
        }
    }

    /**
     * 应用过滤器
     *
     * @param Builder $builder
     * @param Filter $filter
     *
     * @return mixed
     */
    public function scopeFilter(Builder $builder, Filter $filter)
    {
        return $filter->apply($builder);
    }

    /**
     * 批量更新
     * 减少 n - 1 次 quereis
     * 
     * @param string values [id => params]
     * @param string column  要更新的字段名称
     */
    public static function updateValues(array $values, string $column)
    {
        $model = static::getModel();
        $table = $model->getTable();

        $ids = [];
        $cases = '';

        foreach ($values as $id => $value) {
            $ids[] = (int) $id;
            $cases .= ' WHEN `id`=' . end($ids) . ' then "' . $value . '"';
        }

        $ids = implode(',', $ids);

        if ($model->timestamp) {
            return DB::update("UPDATE `{$table}`  SET  `{$column}` = CASE {$cases} END, `updated_at` = ? WHERE `id` in ({$ids})", [now()]);
        }

        return DB::update("UPDATE `{$table}`  SET  `{$column}` = CASE {$cases} END WHERE `id` in ({$ids})");
    }
}

<?php

namespace Rbac\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * 嵌套结构模型辅助 trait.
 *
 * Trait ModelTree
 */
trait ModelTree
{
    /**
     * 要排除的节点 id，子元素都会被排除.
     *
     * @var int
     */
    protected $except = 0;
    protected static $branchOrder = [];

    protected function parentColumn()
    {
        return 'parent_id';
    }

    protected function orderColumn()
    {
        return 'order';
    }

    protected function idColumn()
    {
        return $this->getKeyName();
    }

    /**
     * 排除指定的 id，排除后，该 id 和其子孙记录，都会排除.
     *
     * @return $this
     */
    public function treeExcept($id)
    {
        $this->except = $id;

        return $this;
    }

    /**
     * 构建嵌套数组.
     */
    public function toTree(): array
    {
        $nodes = $this->allNodes();

        return $this->buildNestedArray($nodes);
    }

    /**
     * 构建嵌套数组.
     *
     * @param int $parentId
     */
    protected function buildNestedArray(array $nodes = [], $parentId = 0): array
    {
        $branch = [];
        static $parentIds;
        $parentIds = $parentIds ?: array_flip(array_column($nodes, $this->parentColumn()));

        foreach ($nodes as $node) {
            if ($this->ignoreTreeNode($node)) {
                continue;
            }

            if ($node[$this->parentColumn()] == $parentId) {
                $children = $this->buildNestedArray($nodes, $node[$this->idColumn()]);
                // 没有子菜单也显示一个空的数组，避免前端没有 children 时，不能响应式
                $node['children'] = $children;
                $branch[] = $node;
            }
        }

        return $branch;
    }

    /**
     * 按排序查出所有记录.
     */
    protected function allNodes(): array
    {
        // return $this->allNodesQuery()->get();
        return (array)$this->allNodesQuery()->get()->toArray();
    }

    /**
     * @return Builder|mixed
     */
    protected function allNodesQuery(): Builder
    {
        return static::query()
            ->when($this->except, function (Builder $query) {
                $query->where($this->idColumn(), '<>', $this->except)
                    ->where($this->parentColumn(), '<>', $this->except);
            })
            ->orderBy($this->orderColumn());
    }

    public function children()
    {
        return $this->hasMany(static::class, $this->parentColumn(), $this->idColumn());
    }

    public function parent()
    {
        return $this->belongsTo(static::class, $this->parentColumn(), $this->idColumn());
    }

    public function delete()
    {
        $this->children->each->delete();

        return parent::delete();
    }

    /**
     * 是否跳过节点的处理.
     *
     * @param array $node 当前节点
     */
    protected function ignoreTreeNode(array $node): bool
    {
        return false;
    }

    protected function setBranchOrder(array $order)
    {
        static::$branchOrder = array_flip(Arr::flatten($order));

        static::$branchOrder = array_map(function ($item) {
            return ++$item;
        }, static::$branchOrder);
    }

    public function saveOrder($tree = [], $parentId = 0)
    {
        if (empty(static::$branchOrder)) {
            $this->setBranchOrder($tree);
        }

        foreach ($tree as $branch) {
            /** @var ModelTree $node */
            $node = static::find($branch[$this->idColumn()]);

            $node->{$node->parentColumn()} = $parentId;
            $node->{$node->orderColumn()} = static::$branchOrder[$branch[$this->idColumn()]];
            $node->save();

            if (isset($branch['children'])) {
                static::saveOrder($branch['children'], $branch[$this->idColumn()]);
            }
        }
    }

    /**
     * toTree 的反向操作.
     */
    public function flatten(array $tree): array
    {
        $flatten = [];

        foreach ($tree as $item) {
            $children = Arr::pull($item, 'children', []);
            $flatten[] = $item;
            $flatten = array_merge($flatten, $this->flatten($children));
        }

        return $flatten;
    }

    /*
     * 通过直接父id 获取所有祖先数组
     *
     * @param string parent_id
     * @param array parents
     *
     */
    public static function getParents(string $parent_id, &$parents = [])
    {
        if (is_null($parent_id) || $parent_id == 0) {
            return;
        }

        if ($parent = static::DBFind($parent_id)) {
            array_unshift($parents, $parent);
            static::getParents($parent->parent_id, $parents);
        }
    }

    /**
     * 通过主键 id 获取到自身以及所有祖先数组.
     *
     * @param string id
     */
    public static function getParentsAndSelf(string $id): array
    {

        if ($self = static::DBFind($id)) {
            $parents = [];
            static::getParents($self->parent_id, $parents);
            $parents[] = $self;

            return $parents;
        }

        return [];
    }

    /**
     * 通过 DB 类用 id 查询数据，避免触发模型事件
     *
     * @param mixed id
     */
    public static function DBFind($id)
    {
        return DB::table((new static)->getTable())->select('_id', 'parent_id', 'name', 'code')->whereId($id)->first();
    }

    /**
     * 根据 id 获取到所有子分类 id
     *
     * @param integer id
     *
     * @return arrayAcess
     */
    public static function getAllChildBatch(array $ids, &$subs = [])
    {
        foreach ($ids as $id) {
            static::getAllChild($id, $subs);
        }
    }

    /**
     * 根据 id 获取到所有子分类 id
     *
     * @param integer id
     *
     * @return arrayAcess
     */
    public static function getAllChild(int $id, &$subs = [])
    {
        $children = static::getChildren($id);
        if (count($children) <= 0) {
            return;
        }

        $subs = array_merge($subs, $children->pluck('_id')->all());

        foreach ($children as $child) {
            static::getAllChild($child->id, $subs);
        }
    }


    /**
     * 根据 id 从缓存中获取当前分类的所有直接子分类
     *
     * @param integer id
     *
     * @return Collection
     */
    public static function getChildren(int $id)
    {
        // return static::getCached(['parent_id' => $id]);
        return static::where('parent_id', $id)->get();
    }
}

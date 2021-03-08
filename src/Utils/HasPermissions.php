<?php

/**
 * 来自 laravel-admin
 */

namespace Rbac\Utils;

use Illuminate\Support\Collection;
use Rbac\Enums\RoleSlug;

trait HasPermissions
{
    /**
     * 获取所有权限, 包含角色中的
     *
     * @return Collection
     */
    public function allPermissions()
    {
        $role = $this->isAdministrator() ? $this->roles() : $this->groupRole();
        return $role->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->merge($this->permissions)
            ->unique('id')
            ->values();
    }

    /**
     * 判断是否有某个权限
     *
     * @param string $ability
     * @param array $arguments
     *
     * @return bool
     */
    public function can($ability, $arguments = [])
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if ($this->permissions->pluck('slug')->contains($ability)) {
            return true;
        }

        $permissions = $this->isAdministrator() ? $this->roles->pluck('permissions') : $this->groupRole->permissions;

        return $permissions->flatten()->pluck('slug')->contains($ability);
    }

    /**
     * 判断是不是没权限
     *
     * @param string $ability
     * @param array $arguments
     *
     * @return bool
     */
    public function cannot($ability, $arguments = [])
    {
        return !$this->can($ability);
    }

    /**
     * 判断是不是超级管理员
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return $this->isRole(RoleSlug::Administrator);
    }

    /**
     * 判断是不是特定的角色
     *
     * @param string $role
     *
     * @return bool
     */
    public function isRole(string $role)
    {
        $slug = $this->roles->count() ? $this->roles : $this->groupRole();
        return $slug->pluck('slug')->contains($role);
    }

    /**
     * 判断是不是角色之一
     *
     * @param array $roles
     *
     * @return bool
     */
    public function inRoles($roles = [])
    {
        $slug = $this->roles->count() ? $this->roles : $this->groupRole();
        return $slug->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    /**
     * 判断是不是能通过这些角色的验证
     *
     * @param mixed $roles 角色数组, 其中元素应该是有 slug 键的 ArrayAccess
     *
     * @return bool
     */
    public function visible($roles = [])
    {
        if (empty($roles)) {
            return true;
        }

        $roles = array_column($roles, 'slug');

        return $this->inRoles($roles) || $this->isAdministrator();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->roles()->detach();

            $model->permissions()->detach();
        });
    }
}

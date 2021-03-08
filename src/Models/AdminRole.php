<?php

namespace Rbac\Models;

class AdminRole extends Model
{
    protected $fillable = ['name', 'slug'];

    public function permissions()
    {
        return $this->belongsToMany(
            AdminPermission::class,
            'admin_role_permission',
            'role_id',
            'permission_id'
        );
    }

    public function delete()
    {
        $this->permissions()->detach();
        return parent::delete();
    }

    /**
     * 通过角色id 判断是不是期望的 slug
     */
    public static function isReviewer(int $role_id)
    {
        return static::query()->where('id', $role_id)->value('slug') === 'reviewer';
    }
}

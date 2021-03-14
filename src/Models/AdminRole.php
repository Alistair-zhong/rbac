<?php

namespace Rbac\Models;

class AdminRole extends Model
{
    protected $fillable = ['name', 'slug', 'user_ids', 'vue_router_ids', 'permission_ids'];

    public function permissions()
    {
        return $this->belongsToMany(
            AdminPermission::class,
            null,
            'role_ids',
            'permission_ids'
        );
    }

    public function routers()
    {
        return $this->belongsToMany(
            VueRouter::class,
            null,
            'role_ids',
            'vue_router_ids'
        );
    }

    public function delete()
    {
        $this->permissions()->detach();
        return parent::delete();
    }

    public function getRouteKeyName()
    {
        return $this->getKeyName();
    }
}

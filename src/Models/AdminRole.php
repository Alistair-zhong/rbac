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

    public function routers()
    {
        return $this->belongsToMany(
            VueRouter::class,
            'vue_router_role',
            'role_id',
            'vue_router_id'
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

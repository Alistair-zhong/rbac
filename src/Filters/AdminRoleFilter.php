<?php

namespace Rbac\Filters;

use Rbac\Filters\Traits\RolePermissionFilter;

class AdminRoleFilter extends Filter
{
    use RolePermissionFilter;
    protected $simpleFilters = [
        'id',
        'name' => ['like', '%?%'],
        'slug' => ['like', '%?%'],
    ];
    protected $filters = ['permission_name'];
}

<?php

namespace Rbac\Filters;

use Rbac\Filters\Traits\RolePermissionFilter;

class AdminUserFilter extends Filter
{
    use RolePermissionFilter;
    protected $simpleFilters = [
        'id',
        'username' => ['like', '%?%'],
        'name' => ['like', '%?%'],
    ];
    protected $filters = [
        'role_name',
        'permission_name',
    ];
}

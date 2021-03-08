<?php

namespace Rbac\Enums;

use Rbac\Enum\Enum;

class RoleSlug extends Enum
{
    const Administrator = 'administrator';  // 超管
    const Designer      = 'designer';       // 设计人员
    const Reviewer      = 'reviewer';       // 审核人员（版权员）
    const Manager       = 'manager';        // 管理人员
    const Leader        = 'leader';         // 最高管理人员（组长）
}

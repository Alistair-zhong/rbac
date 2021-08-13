<?php

namespace Rbac\Enums;

use MyCLabs\Enum\Enum;

class RoleSlug extends Enum
{
    const SuperAdmin    = 'super_admin';
    const Leader        = 'leader';
    const Administrator = 'admin';
    const User          = 'user';
    const Agent         = 'livechat_agent';
}

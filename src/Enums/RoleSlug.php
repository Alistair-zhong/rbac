<?php

namespace Rbac\Enums;

use MyCLabs\Enum\Enum;

class RoleSlug extends Enum
{
    const Administrator = 'administrator';  // 超管
    const Manager       = 'manager';
    const Editor        = 'editor';
}

<?php

namespace Rbac\Enums;

use MyCLabs\Enum\Enum;

class RoleSlug extends Enum
{
    const Administrator = 'administrator';
    const Manager       = 'manager';
    const Editor        = 'editor';
}

<?php

namespace Rbac\Models;

use Rbac\Models\AdminRole;
use Rbac\Traits\ModelHelpers;
use Rbac\Utils\HasPermissions;
use Rbac\Models\AdminPermission;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticator
{
    use HasPermissions;
    use Notifiable;
    use ModelHelpers;

    public function receivesBroadcastNotificationsOn()
    {
        return 'user.' . $this->getKey();
    }


    protected $fillable = [
        'username', //账号
        'password', // 密码
        'name', // 用户名
        'avatar', // 图像
        'country', // 国家
        'created_by', // 创建人
        'updated_by', // 更新人
        'role_ids',
        'permission_ids',
        'page_id',
        'psids',
        'pages',

    ];

    public function roles()
    {
        return $this->belongsToMany(
            AdminRole::class,
            null,
            'user_ids',
            'role_ids'
        );
    }

    public function permissions()
    {
        return $this->belongsToMany(
            AdminPermission::class,
            null,
            'user_ids',
            'permission_ids'
        );
    }

    /**
     * 从请求数据中添加用户.
     *
     * @param array $inputs
     * @param bool  $hashedPassword 传入的密码, 是否是没有哈希处理的明文密码
     *
     * @return AdminUser|\Illuminate\Database\Eloquent\Model
     */
    public static function createUser($inputs, $hashedPassword = false)
    {
        if (!$hashedPassword) {
            $inputs['password'] = bcrypt($inputs['password']);
        }

        return static::create($inputs);
    }

    /**
     * 从请求数据中, 更新一条记录.
     *
     * @param array $inputs
     * @param bool  $hashedPassword 传入的密码, 是否是没有哈希处理的明文密码
     *
     * @return bool
     */
    public function updateUser($inputs, $hashedPassword = false)
    {
        // 更新时, 填了密码, 且没有经过哈希处理
        if (
            isset($inputs['password']) &&
            !$hashedPassword
        ) {
            $inputs['password'] = bcrypt($inputs['password']);
        }

        return $this->update($inputs);
    }
}

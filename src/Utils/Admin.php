<?php

namespace Rbac\Utils;

use Rbac\Enums\RoleSlug;
use Illuminate\Support\Str;

class Admin
{
    /**
     * 当前登录管理员
     *
     * @return \App\Admin\Models\AdminUser|\Illuminate\Contracts\Auth\Authenticatable
     */
    public static function user()
    {
        return static::guard()->user();
    }

    /**
     * 当前登录管理员是不是超级管理员
     *
     * @return boolean
     */
    public static function isSuperAdmin(): bool
    {
        $user = static::user();
        return $user && $user->isSuperAdmin();
    }

    /**
     * 当前登录管理员是不是管理员
     *
     * @return bool
     */
    public static function isAdministrator()
    {
        return static::user() && static::user()->isAdministrator();
    }

    /**
     * 把路径自动拼上后端的路径前缀
     *
     * @param string $path
     *
     * @return string
     */
    public static function url($path = '')
    {
        $prefix = 'admin-api';
        $path = trim($path, '/');

        if (is_null($path) || strlen($path) == 0) {
            $path = $prefix;
        } else {
            $path = $prefix . '/' . $path;
        }

        return $path;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public static function guard()
    {
        return auth('admin');
    }

    /**
     * 获取管理员目录路径
     *
     * @param $path
     *
     * @return string
     */
    public static function path(string $path = '')
    {
        return app_path('Admin' . ($path ? (DIRECTORY_SEPARATOR . ltrim($path, '\/')) : ''));
    }

    public static function urlWithMethod(string $path = ''): string
    {
        if (Str::contains($path, ':')) {
            [$method, $path] = explode(':', $path);
            return $method . ':' . static::url($path);
        } else {
            return static::url($path);
        }
    }
}

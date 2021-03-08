<?php

namespace Rbac\Commands;

use Rbac\Models\AdminPermission;
use Rbac\Models\AdminRole;
use Rbac\Models\AdminUser;
use Rbac\Models\Config;
use Rbac\Models\ConfigCategory;
use Rbac\Models\VueRouter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RbacInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化基础路由配置，超级管理员角色和权限';

    public static $initConfirmTip = '初始化操作，会清空路由、管理员、角色和权限表，以及相关关联表数据。是否确认？';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm(static::$initConfirmTip)) {
            $this->createVueRouters();
            $this->createUserRolePermission();
            $this->createDefaultConfigs();
            $this->info('初始化完成，管理员为：admin，密码为：000000');

            return 1;
        } else {
            return 0;
        }
    }

    protected function createVueRouters()
    {
        $keys = [
            '首页' => new \MongoDB\BSON\ObjectID(),

            '路由配置' => new \MongoDB\BSON\ObjectID(),
            '所有路由' => new \MongoDB\BSON\ObjectID(),
            '添加路由' => new \MongoDB\BSON\ObjectID(),
            '编辑路由' => new \MongoDB\BSON\ObjectID(),

            '管理员管理' => new \MongoDB\BSON\ObjectID(),
            '管理员列表' => new \MongoDB\BSON\ObjectID(),
            '添加管理员' => new \MongoDB\BSON\ObjectID(),
            '编辑管理员' => new \MongoDB\BSON\ObjectID(),

            '角色管理' => new \MongoDB\BSON\ObjectID(),
            '角色列表' => new \MongoDB\BSON\ObjectID(),
            '添加角色' => new \MongoDB\BSON\ObjectID(),
            '编辑角色' => new \MongoDB\BSON\ObjectID(),

            '权限管理' => new \MongoDB\BSON\ObjectID(),
            '权限列表' => new \MongoDB\BSON\ObjectID(),
            '添加权限' => new \MongoDB\BSON\ObjectID(),
            '编辑权限' => new \MongoDB\BSON\ObjectID(),

            '文件管理' => new \MongoDB\BSON\ObjectID(),

            '配置管理' => new \MongoDB\BSON\ObjectID(),
            '配置分类' => new \MongoDB\BSON\ObjectID(),
            '所有配置' => new \MongoDB\BSON\ObjectID(),
            '添加配置' => new \MongoDB\BSON\ObjectID(),
            '编辑配置' => new \MongoDB\BSON\ObjectID(),

            '系统设置' => new \MongoDB\BSON\ObjectID(),
        ];

        $inserts = [
            [$keys['首页'], 0, '首页', 'index', 0, null, 1],

            [$keys['路由配置'], 0, '路由配置', null, 1, null, 1],
            [$keys['所有路由'], $keys['路由配置'], '所有路由', 'vue-routers', 2, null, 1],
            [$keys['添加路由'], $keys['路由配置'], '添加路由', 'vue-routers/create', 3, null, 1],
            [$keys['编辑路由'], $keys['路由配置'], '编辑路由', 'vue-routers/:id(\\d+)/edit', 4, null, 0],

            [$keys['管理员管理'], 0, '管理员管理', null, 5, null, 1],
            [$keys['管理员列表'], $keys['管理员管理'], '管理员列表', 'admin-users', 6, null, 1],
            [$keys['添加管理员'], $keys['管理员管理'], '添加管理员', 'admin-users/create', 7, null, 1],
            [$keys['编辑管理员'], $keys['管理员管理'], '编辑管理员', 'admin-users/:id(\\d+)/edit', 8, null, 0],

            [$keys['角色管理'], 0, '角色管理', null, 9, null, 1],
            [$keys['角色列表'], $keys['角色管理'], '角色列表', 'admin-roles', 10, null, 1],
            [$keys['添加角色'], $keys['角色管理'], '添加角色', 'admin-roles/create', 11, null, 1],
            [$keys['编辑角色'], $keys['角色管理'], '编辑角色', 'admin-roles/:id(\\d+)/edit', 12, null, 0],

            [$keys['权限管理'], 0, '权限管理', null, 13, null, 1],
            [$keys['权限列表'], $keys['权限管理'], '权限列表', 'admin-permissions', 14, null, 1],
            [$keys['添加权限'], $keys['权限管理'], '添加权限', 'admin-permissions/create', 15, null, 1],
            [$keys['编辑权限'], $keys['权限管理'], '编辑权限', 'admin-permissions/:id(\\d+)/edit', 16, null, 0],

            [$keys['文件管理'], 0, '文件管理', 'system-media', 17, null, 1],

            [$keys['配置管理'], 0, '配置管理', null, 18, null, 1],
            [$keys['配置分类'], $keys['配置管理'], '配置分类', 'config-categories', 19, null, 1],
            [$keys['所有配置'], $keys['配置管理'], '所有配置', 'configs', 20, null, 1],
            [$keys['添加配置'], $keys['配置管理'], '添加配置', 'configs/create', 21, null, 1],
            [$keys['编辑配置'], $keys['配置管理'], '编辑配置', 'configs/:id(\\d+)/edit', 22, null, 0],

            [$keys['系统设置'], 0, '系统设置', '/configs/system_basic', 23, null, 1],
        ];

        $inserts = $this->combineInserts(
            [(new VueRouter)->getKeyName(), 'parent_id', 'title', 'path', 'order', 'icon', 'menu'],
            $inserts,
            [
                'cache' => 0,
                // 'created_at' => now(),
                // 'created_at' => now(),
                'updated_at' => new \MongoDB\BSON\UTCDateTime(new \DateTime('now')),
                'created_at' => new \MongoDB\BSON\UTCDateTime(new \DateTime('now')),
                'permission' => null,
            ]
        );

        VueRouter::truncate();
        VueRouter::insert($inserts);
    }

    protected function createUserRolePermission()
    {
        AdminUser::truncate();
        AdminRole::truncate();
        AdminPermission::truncate();

        collect(['admin_role_permission', 'admin_user_permission', 'admin_user_role', 'vue_router_role'])
            ->each(function ($table) {
                DB::table($table)->truncate();
            });

        $user = AdminUser::create([
            'name' => '管理员',
            'username' => 'admin',
            'password' => bcrypt('000000'),
            'country' => rand(1, 10) % 2 ? 'US' : 'KR',
        ]);

        $user->roles()->create([
            'name' => '超级管理员',
            'slug' => 'administrator',
        ]);

        AdminRole::first()
            ->permissions()
            ->create([
                'name' => '所有权限',
                'slug' => 'pass-all',
                'http_path' => '*',
            ]);
    }

    protected function createDefaultConfigs()
    {
        $category_id = new \MongoDB\BSON\ObjectID();
        $categories = [
            [$category_id, '系统设置', 'system_basic'],
        ];
        $categories = $this->combineInserts(
            [(new ConfigCategory)->getKeyName(), 'name', 'slug'],
            $categories,
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $configs = [
            [$category_id, Config::TYPE_INPUT, '系统名称', 'app_name', null, json_encode('后台'), 'required|string|max:20'],
            [$category_id, Config::TYPE_FILE, '系统 LOGO', 'app_logo', '{"max":1,"ext":"jpg,png,jpeg"}', null, 'nullable|string'],
            [$category_id, Config::TYPE_OTHER, '首页路由', 'home_route', null, json_encode('1'), 'required|exists:vue_routers,id'],
            [$category_id, Config::TYPE_INPUT, 'CDN 域名', 'cdn_domain', null, json_encode('/'), 'required|string'],
            [
                $category_id, Config::TYPE_SINGLE_SELECT, '后台登录验证码', 'admin_login_captcha',
                json_encode([
                    'options' => "1=>开启\n0=>关闭",
                    'type' => 'input',
                ]),
                json_encode('1'), 'required|string',
            ],
        ];
        $configs = $this->combineInserts(
            ['category_id', 'type', 'name', 'slug', 'options', 'value', 'validation_rules'],
            $configs,
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        ConfigCategory::truncate();
        ConfigCategory::insert($categories);

        Config::truncate();
        Config::insert($configs);
    }

    /**
     * 组合字段和对应的值
     *
     * @param array $fields  字段
     * @param array $inserts 值，不带字段的
     * @param array $extra   每列都相同的数据，带字段
     */
    protected function combineInserts(array $fields, array $inserts, array $extra): array
    {
        return array_map(function ($i) use ($fields, $extra) {
            $i = array_combine($fields, $i);

            return array_merge($i, $extra);
        }, $inserts);
    }
}

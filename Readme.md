## RBAC for Backend

此工具包开箱即用，集成了接口级别的权限控制模块，自带模型、数据表、中间件，只需要配置数据库账户密码即可使用。


### 安装

    COMPOSER_MEMORY_LIMIT=-1 composer require --no-cache niro/rbac-backend

### 前提

* `php` 需要开启 `mongodb` 扩展

* 需要名为 `login` 的路由，例如

```php
Route::post('auth/login', [C\Auth\LoginController::class, 'login'])->name('login');
```


### 使用说明

* 检查是否在 `env` 中配置了 `mongodb` 数据库账密

* 检查 `config/database.php` 的 `connection` 是否配置了 `mongodb`，如下案例

```php
'connections' => [
    ...
        'mongodb' => [
            'driver' => 'mongodb',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 27017),
            'database' => env('DB_DATABASE', 'chatbot'),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'options' => [
                // here you can pass more settings to the Mongo Driver Manager
                // see https://www.php.net/manual/en/mongodb-driver-manager.construct.php under "Uri Options" for a list of complete parameters that you can use

                // 'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'), // required with Mongo 3+
            ],
        ],

    ],
```

* 执行 `php artisan migrate` 

* 执行 `php artisan rbac:init`

* 恭喜你，完成了初始化



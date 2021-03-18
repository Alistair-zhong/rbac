<?php

namespace Rbac;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rbac\Commands\RbacInitCommand;
use Illuminate\Http\JsonResponse;

class RbacServiceProvider extends ServiceProvider
{
    protected $middlewareMap = [
        'rbac.permission' => Middleware\AdminPermission::class,
        'rbac.auth' => Middleware\Authenticate::class,
    ];

    protected $middlewareGroups = [
        'rbac' => [
            Middleware\ForceJson::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    public function boot()
    {
        $this->loadMiddlewares();
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadConfigs();
        $this->extend();

        if ($this->app->runningInConsole()) {
            $this->commands([
                RbacInitCommand::class,
            ]);
        }
    }

    private function loadMiddlewares()
    {
        foreach ($this->middlewareMap as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }

    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        });
    }

    private function routeConfiguration()
    {
        return [
            'prefix' => 'admin-api',
            'as' => 'admin-api.',
            'namespace' => "Rbac\Controllers",
            'middleware' => [
                // 'rbac.auth',
                // 'rbac.permission',
            ],
        ];
    }

    /**
     * load config
     */
    private function loadConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../configs/auth.php', 'auth');
        $this->mergeConfigFrom(__DIR__ . '/../configs/database.php', 'database');
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);

        $this->app['config']->set($key, $this->mergeConfig($config, require $path));
    }

    /**
     * Merges the configs together and takes multi-dimensional arrays into account.
     *
     * @param  array  $original
     * @param  array  $merging
     * @return array
     */
    protected function mergeConfig(array $original, array $merging)
    {
        $array = array_merge($original, $merging);

        foreach ($original as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (!Arr::exists($merging, $key)) {
                continue;
            }

            if (is_numeric($key)) {
                continue;
            }

            $array[$key] = $this->mergeConfig($value, $merging[$key]);
        }

        return $array;
    }

    protected function extend()
    {
        JsonResponse::macro('wrap', function () {
            $original = [];

            if (isset($this->original['data'])) {
                $original['items'] = $this->original['data'];
                unset($this->original['data']);
                $original = array_merge($original, array_filter((array)$this->original));
            } else if ($this->original instanceof \ArrayObject) {
                // 解决空的情况
            } elseif (is_array($this->original)) {
                $original = (array)$this->original;
            } else {
                $original = $this->original instanceof \Illuminate\Database\Eloquent\Model ? json_decode($this->data) : $this->original->resolve() ?? null;
            }

            $data = [
                'code'    => 0,
                'result'  => $original,
                'message' => 'ok',
                'type'    => 'success'
            ];
            $this->setData($data);

            return $this;
        });
    }
}

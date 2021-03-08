<?php

namespace Rbac;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rbac\Commands\RbacInitCommand;

class RbacServiceProvider extends ServiceProvider
{


    public function boot()
    {
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadConfigs();

        if ($this->app->runningInConsole()) {
            $this->commands([
                RbacInitCommand::class,
            ]);
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
            'middleware' => ['web'],
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
}

<?php

namespace Shuxiaoyuan666\Implogs;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ImplogsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/implog.php', 'implog'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $router->aliasMiddleware('implog', \Shuxiaoyuan666\Implogs\Middleware\ImpRequestLogMiddleware::class);

        $this->publishes([
            __DIR__ . '/Config/implog.php' => config_path('implog.php'),
        ], 'implog_config');

    }
}

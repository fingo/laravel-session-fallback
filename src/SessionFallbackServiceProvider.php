<?php

namespace Fingo\LaravelSessionFallback;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Session\SessionServiceProvider;

class SessionFallbackServiceProvider extends SessionServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('SessionFallback', SessionFallbackFacade::class);
        });
        $this->publishes([
            __DIR__ . '/config/session_fallback.php' => config_path('session_fallback.php'),
        ]);
    }

    /**
     * Register
     *
     * @return mixed
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/session_fallback.php', 'session_fallback');

        $this->app->singleton('session', function ($app) {
            return new SessionFallback($app);
        });

        $this->registerSessionDriver();

        $this->app->singleton('Illuminate\Session\Middleware\StartSession');
    }
}

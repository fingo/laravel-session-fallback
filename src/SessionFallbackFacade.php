<?php

namespace Fingo\LaravelSessionFallback;

use Illuminate\Support\Facades\Facade;

/**
 * Class SessionFallbackFacade
 * @package Fingo\SessionFallback
 */
class SessionFallbackFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-session-fallback';
    }
}

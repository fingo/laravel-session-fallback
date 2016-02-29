<?php

namespace Fingo\LaravelSessionFallback;

use Exception;
use Illuminate\Cache\RedisStore;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\SessionManager;

/**
 * Class SessionFallback
 * @package Fingo\LaravelSessionFallback
 */
class SessionFallback extends SessionManager
{

    /**
     * @var array Array of cache based stores
     */
    protected static $expectedStores = [
        'redis' => 'Illuminate\Redis\Database',
        'memcached' => 'Illuminate\Memcached\Database',
        'apc' => 'Illuminate\Cache\ApcWrapper'
    ];

    /**
     * Create a new driver instance.
     *
     * @param  string $driver
     * @return mixed
     *
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    protected function createDriver($driver)
    {
        try {
            return parent::createDriver($driver);
        } catch (Exception $e) {
            if ($newDriver = $this->nextDriver($driver)) {
                return $this->createDriver($newDriver);
            }
            throw $e;
        }
    }

    /**
     * Create the cache based session handler instance.
     *
     * @param string $driver
     * @return \Illuminate\Session\CacheBasedSessionHandler
     */
    protected function createCacheHandler($driver)
    {
        $handler = parent::createCacheHandler($driver);
        if (!$this->validateCacheHandler($driver, $handler)) {
            throw new \UnexpectedValueException('Wrong cache driver found');
        }
        return $handler;
    }


    /**
     * Create an instance of the Redis session driver.
     *
     * @return \Illuminate\Session\Store
     */
    protected function createRedisDriver()
    {
        $handler = $this->createCacheHandler('redis');
        if (!is_a($handler->getCache()->getStore(), RedisStore::class)) {
            throw new \UnexpectedValueException('Wrong cache driver found');
        }
        $handler->getCache()->getStore()->getRedis()->ping();
        $handler->getCache()->getStore()->setConnection($this->app['config']['session.connection']);
        return $this->buildSession($handler);
    }

    /**
     * @param $driver
     * @return bool
     */
    public function nextDriver($driver)
    {
        $driverOrder = config('session_fallback.fallback_order');
        if (in_array($driver, $driverOrder, true) && last($driverOrder) !== $driver) {
            $nextKey = array_search($driver, $driverOrder, true) + 1;
            return $driverOrder[$nextKey];
        }
        return false;
    }

    /**
     * Get next driver name based on fallback order
     *
     * @return \Illuminate\Session\Store
     */
    protected function createDatabaseDriver()
    {
        $connection = $this->getDatabaseConnection();
        $connection->getReadPdo();
        $table = $this->app['config']['session.table'];
        return $this->buildSession(new DatabaseSessionHandler($connection, $table, $this->app));

    }

    /**
     * Check if session has right store
     *
     * @param $driver
     * @param $handler
     * @return bool
     */
    public function validateCacheHandler($driver, $handler)
    {
        $store = $handler->getCache()->getStore();
        return static::$expectedStores[$driver] !== get_class($store);
    }
}

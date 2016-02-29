<?php

namespace Test;

use Fingo\LaravelSessionFallback\SessionFallback;
use Fingo\LaravelSessionFallback\SessionFallbackServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase;

class CacheFallbackTest extends TestCase
{

    protected $application;

    public function setUp()
    {
        parent::setUp();
        $this->application = $this->createApplication();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('session.driver', 'redis');
        $app['config']->set('app.key', 'hh8oYDaXmHZQ6uNhaq7HWtpDDucMtD5C');
    }

    protected function getPackageProviders($app)
    {
        return [SessionFallbackServiceProvider::class];
    }

    public function testRedisDriver()
    {
        $mock = Mockery::mock('overload:Predis\Client');
        $mock->shouldReceive('ping')->andReturn(true);
        $session = new SessionFallback($this->application);
        $handler = $session->driver()->getHandler();
        $this->assertInstanceOf('Illuminate\Session\CacheBasedSessionHandler', $handler);
        $store = $session->driver()->getHandler()->getCache()->getStore();
        $this->assertInstanceOf('Illuminate\Cache\RedisStore', $store);
    }

    public function testMemcacheDriver()
    {
        $this->createFailRedis();
        $this->createSuccessMemcached();
        $session = new SessionFallback($this->application);
        $handler = $session->driver()->getHandler();
        $this->assertInstanceOf('Illuminate\Session\CacheBasedSessionHandler', $handler);
        $store = $session->driver()->getHandler()->getCache()->getStore();
        $this->assertInstanceOf('Illuminate\Cache\MemcachedStore', $store);
    }

    public function testCookieDriver()
    {
        $this->createFailRedis();
        $this->createFailMemcached();
        $session = new SessionFallback($this->application);
        $handler = $session->driver()->getHandler();
        $this->assertInstanceOf('Illuminate\Session\CookieSessionHandler', $handler);
    }

    private function createSuccessMemcached()
    {
        $mockMemcached = Mockery::mock('overload:Illuminate\Cache\MemcachedConnector');
        $mockMemcached->shouldReceive('connect')->andReturn(true);
    }
    private function createFailMemcached()
    {
        $mockMemcached = Mockery::mock('overload:Illuminate\Cache\MemcachedConnector');
        $mockMemcached->shouldReceive('connect')->andThrow('Exception');
    }

    private function createFailRedis()
    {
        $mockRedis = Mockery::mock('overload:Predis\Client');
        $mockRedis->shouldReceive('ping')->andThrow('Exception');
    }
}

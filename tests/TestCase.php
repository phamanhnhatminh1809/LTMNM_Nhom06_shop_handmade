<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        // Bảo vệ MySQL dev/demo: ép PHPUnit luôn chạy trên SQLite :memory: dù đang trong Docker container
        $overrides = [
            'APP_ENV'          => 'testing',
            'APP_CONFIG_CACHE' => sys_get_temp_dir() . '/phpunit-no-config.php',
            'APP_ROUTES_CACHE' => sys_get_temp_dir() . '/phpunit-no-routes.php',
            'APP_EVENTS_CACHE' => sys_get_temp_dir() . '/phpunit-no-events.php',
            'DB_CONNECTION'    => 'sqlite',
            'DB_DATABASE'      => ':memory:',
            'DB_HOST'          => '',
            'DB_PORT'          => '',
            'DB_USERNAME'      => '',
            'DB_PASSWORD'      => '',
            'CACHE_STORE'      => 'array',
            'SESSION_DRIVER'   => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ];

        foreach ($overrides as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        return parent::createApplication();
    }
}

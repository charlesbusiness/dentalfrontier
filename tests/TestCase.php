<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prevent Sanctum infinite recursion in Feature tests
        Config::set('auth.guards.sanctum.driver', 'session');
    }
}
